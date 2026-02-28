<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\BaseController;
use App\Models\AppAsset;

class AppAssetController extends BaseController
{
    /**
     * Get all active app assets.
     *
     * Returns a key→{url, version} map so Flutter can compare
     * version hashes and only re-download changed images.
     */
    public function index()
    {
        $assets = AppAsset::where('is_active', true)
            ->get()
            ->keyBy('key')
            ->map(fn ($a) => [
                'url'     => $a->image_url,
                'version' => $a->version_hash,
                'label'   => $a->label,
            ]);

        return $this->sendResponse($assets, 'App assets retrieved successfully');
    }
}
