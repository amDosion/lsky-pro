<?php

namespace App\Services;

use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * 删除图片以及物理文件，返回已删除数量
     * 考虑到删除磁盘文件、服务器内部请求第三方删除接口比较消耗资源，传入的主键不应过多
     * TODO 改进循环中更新数据
     *
     * @param array $keys
     * @param User|null $user 传入用户数据则会根据用户id过滤
     * @param string $field
     * @return int
     */
    public function deleteImages(array $keys, ?User $user = null, string $field = 'id', ?int $spaceId = null): int
    {
        $count = 0;
        $useRecycleBin = config('lifecycle.recycle_bin.enabled', false);
        $model = Image::with('user', 'strategy', 'album')->when(! is_null($user), function (Builder $builder) use ($user) {
            $builder->where('user_id', $user->id);
        })->when(! is_null($spaceId), function (Builder $builder) use ($spaceId) {
            $builder->where('space_id', $spaceId);
        })->whereIn($field, $keys);

        DB::transaction(function () use ($model, $useRecycleBin, &$count) {
            $affectedUserIds = [];
            $albumDeleteCounts = [];

            /** @var Image $image */
            foreach ($model->cursor() as $image) {
                if ($image->user_id) {
                    $affectedUserIds[(int) $image->user_id] = true;
                }

                if ($image->album_id) {
                    $albumDeleteCounts[(int) $image->album_id] = ($albumDeleteCounts[(int) $image->album_id] ?? 0) + 1;
                }

                if ($useRecycleBin) {
                    $image->delete();
                } else {
                    $image->forceDelete();
                }

                $count++;
            }

            foreach ($albumDeleteCounts as $albumId => $deletedCount) {
                DB::table('albums')
                    ->where('id', $albumId)
                    ->decrement('image_num', $deletedCount);
            }

            if ($affectedUserIds === []) {
                return;
            }

            $remainingCounts = Image::query()
                ->selectRaw('user_id, COUNT(*) as aggregate')
                ->whereIn('user_id', array_keys($affectedUserIds))
                ->groupBy('user_id')
                ->pluck('aggregate', 'user_id');

            foreach (array_keys($affectedUserIds) as $userId) {
                DB::table('users')
                    ->where('id', $userId)
                    ->update([
                        'image_num' => (int) ($remainingCounts[$userId] ?? 0),
                    ]);
            }
        });

        return $count;
    }
}
