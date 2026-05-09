<?php

declare(strict_types=1);

namespace App\Core\Application\Api\V1\Catalog\Controllers;

use App\Core\Application\Api\Responses\BaseResponse;
use App\Core\Application\Api\V1\Catalog\Queries\ProgramIndexQuery;
use App\Core\Application\Api\V1\Catalog\Resources\ProgramResource;
use App\Core\Domain\Catalog\Models\Program;
use App\Core\Domain\Catalog\States\ProgramStatus;
use App\Core\Domain\Catalog\States\JobOfferStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
   
}