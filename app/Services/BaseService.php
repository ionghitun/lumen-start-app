<?php

namespace App\Services;

use App\Models\Language;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

/**
 * Class BaseService
 *
 * @package App\Services
 */
class BaseService
{
    /**
     * Get pagination offset and limit.
     *
     * @param Request $request
     *
     * @return array
     */
    public function getPaginationParams(Request $request)
    {
        $limit = 10;
        if ($request->has('limit')) {
            $requestLimit = (int)$request->get('limit');

            if ($requestLimit > 0) {
                $limit = $requestLimit;
            }
        }

        $offset = 0;
        $page = 1;

        if ($request->has('page')) {
            $requestPage = (int)$request->get('page');

            if ($requestPage > 1) {
                $page = $requestPage;
            }

            $offset = ($page - 1) * $limit;
        }

        return [
            'page' => $page,
            'offset' => $offset,
            'limit' => $limit
        ];
    }

    /**
     * Apply sort params.
     *
     * @param Request $request
     * @param $builder
     *
     * @return mixed
     */
    public function applySortParams(Request $request, $builder)
    {
        if ($request->has('sortColumn') || $request->has('sortOrder')) {
            $sortColumn = strtolower($request->get('sortColumn', 'id'));
            $sortOrder = strtolower($request->get('sortOrder', 'asc'));

            if (in_array($sortColumn, $builder->getModel()->getSortable()) && in_array($sortOrder, ['asc', 'desc'])) {
                return $builder->orderBy($sortColumn, $sortOrder);
            }
        }

        return $builder;
    }

    /**
     * Get pagination data.
     *
     * @param Builder $builder
     * @param $page
     * @param $limit
     *
     * @return array
     */
    public function getPaginationData(Builder $builder, $page, $limit)
    {
        $totalEntries = $builder->count();

        $totalPages = ceil($totalEntries / $limit);

        return [
            'currentPage' => $page > $totalPages ? $totalPages : $page,
            'totalPages' => $totalPages,
            'limit' => $limit,
            'totalEntries' => $totalEntries
        ];
    }

    /**
     * Get language to use.
     *
     * @param Request $request
     *
     * @return Language
     *
     * @throws Exception
     */
    public function getLanguage(Request $request)
    {
        if ($request->has('language')) {
            $language = Language::where('code', strtolower($request->get('language')))->first();

            if ($language) {
                return $language;
            }
        }

        /** @var User|null $user */
        $user = Auth::user();

        if ($user) {
            $language = Language::where('id', $user->language_id)->first();

            if ($language) {
                return $language;
            }
        }

        $language = Language::where('code', env('APP_LANGUAGE'))->first();

        if ($language) {
            return $language;
        }

        throw new Exception('Application is bad configured!');
    }


    /**
     * Process images.
     *
     * @param $path
     * @param $image
     * @param $name
     * @param bool $generateAvatar
     * @param bool $onlyAvatar
     *
     * @return false|string
     */
    public function processImage($path, $image, $name, $generateAvatar = false, $onlyAvatar = false)
    {
        $pictureData = [];

        if ($generateAvatar) {
            $avatarImage = Image::make($image)->resize(200, 200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            $color = $this->getColorAverage($avatarImage);

            $avatarCanvas = Image::canvas(200, 200, $color);

            $avatarCanvas->insert($avatarImage, 'center');

            $avatarPath = $path . 'avatar/';
            File::makeDirectory($avatarPath, 0777, true, true);

            $avatarCanvas->save($avatarPath . $name);

            $pictureData['avatar'] = $avatarPath . $name;
        }

        if ($onlyAvatar) {
            return json_encode($pictureData);
        }

        $mediumImage = Image::make($image)->resize(1024, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $mediumPath = $path . 'medium/';
        File::makeDirectory($mediumPath, 0777, true, true);

        $mediumImage->save($mediumPath . $name);
        $pictureData['medium'] = $mediumPath . $name;

        $originalMaxImage = Image::make($image)->resize(1920, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $originalPath = $path . 'original/';
        File::makeDirectory($originalPath, 0777, true, true);

        $originalMaxImage->save($originalPath . $name);
        $pictureData['original'] = $originalPath . $name;

        return json_encode($pictureData);
    }

    /**
     * Get average image color.
     *
     * @param $image
     * @return array
     */
    private function getColorAverage($image)
    {
        $image = clone $image;

        $color = $image->limitColors(1)->pickColor(0, 0);
        $image->destroy();

        return $color;
    }
}
