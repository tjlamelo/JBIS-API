<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Domain\Recruiter\Models\RecruiterOrganization;
use App\Core\Domain\Recruiter\Support\RecruiterAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResolveRecruiterTenant
{
    public function __construct(private readonly RecruiterAccess $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        $host = strtolower((string) $request->getHost());
        $organization = $this->access->resolveOrganizationFromApiHost($host);

        if ($organization === null) {
            $slug = $this->access->parseSlugFromApiHost($host);
            if ($slug !== null) {
                $organization = RecruiterOrganization::query()->where('slug', $slug)->first();
            }
        }

        if ($organization === null && app()->environment('local')) {
            $devSlug = $request->header('X-Recruiter-Org');
            if (is_string($devSlug) && $devSlug !== '') {
                $organization = RecruiterOrganization::query()->where('slug', $devSlug)->first();
            }
        }

        if ($organization !== null) {
            $request->attributes->set('recruiterOrganization', $organization);
        }

        $user = $request->user();
        if ($organization !== null && $user !== null && ! $this->access->belongsToOrganization($user, $organization->id)) {
            abort(403, 'Organisation recruteur non autorisée pour ce compte.');
        }

        return $next($request);
    }
}
