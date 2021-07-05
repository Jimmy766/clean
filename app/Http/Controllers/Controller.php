<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Swagger\Annotations as SWG;

/**
 * @SWG\Swagger(
 *   basePath="/api",
 *   @SWG\Info(
 *     title="RAPI",
 *     version="0.3.1"
 *   ),
 *   consumes={"multipart/form-data", "application/x-www-form-urlencoded", "application/json"},
 *   produces={"application/json"},
 *   @SWG\Definition(
 *     definition="Error",
 *     required={"code", "message"},
 *     @SWG\Property(
 *       property="message",
 *       type="string",
 *       description="Message error",
 *     ),
 *     @SWG\Property(
 *       property="code",
 *       type="integer",
 *       format="int32",
 *       description="Code error",
 *     ),
 *   ),
 *   @SWG\Definition(
 *     definition="Message",
 *     required={"code", "message"},
 *     @SWG\Property(
 *       property="code",
 *       type="integer",
 *       format="int32",
 *       description="Code error",
 *     ),
 *     @SWG\Property(
 *       property="message",
 *       type="string",
 *       description="Message error",
 *     )
 *   ),
 *   @SWG\Definition(
 *     definition="Ball",
 *     @SWG\Property(
 *       property="ball",
 *       type="integer",
 *       description="Ball",
 *       example="11",
 *     ),
 *   ),
 *   @SWG\Response(
 *     response="200",
 *     description="Successful operation",
 *     @SWG\Schema(
 *        @SWG\Property(property="data", type="array", @SWG\Items(type="object")),
 *     ),
 *   ),
 *   @SWG\Response(
 *     response="201",
 *     description="Element created",
 *     @SWG\Schema(
 *       @SWG\Property(property="data", type="object", description="Created Element"),
 *     ),
 *   ),
 *   @SWG\Response(
 *     response="400",
 *     description="Bad Request"
 *   ),
 *   @SWG\Response(
 *     response="401",
 *     description="Non authenticated",
 *     @SWG\Schema(
 *       @SWG\Property(property="error", type="string", description="Message error", example="Non authenticated"),
 *       @SWG\Property(property="code", type="integer", description="Response code", example="401"),
 *     ),
 *   ),
 *   @SWG\Response(
 *     response="403",
 *     description="Forbidden Access",
 *     @SWG\Schema(
 *       @SWG\Property(property="error", type="string", description="Message
 * error", example="This #MODEL is not allowed for you"),
 *       @SWG\Property(property="code", type="integer", description="Response code", example="403"),
 *     ),
 *   ),
 *   @SWG\Response(
 *     response="404",
 *     description="URL not found",
 *     @SWG\Schema(
 *       @SWG\Property(property="error", type="string", description="Message error", example="URL not found"),
 *       @SWG\Property(property="code", type="integer", description="Response code", example="404"),
 *     ),
 *   ),
 *   @SWG\Response(
 *     response="405",
 *     description="Specified method is invalid",
 *     @SWG\Schema(
 *       @SWG\Property(property="error", type="string", description="Message error", example="The specified method is invalid"),
 *       @SWG\Property(property="code", type="integer", description="Response code", example="405"),
 *     ),
 *   ),
 *   @SWG\Response(
 *     response="422",
 *     description="Bad Request",
 *     @SWG\Schema(
 *       ref="#/definitions/Error"
 *     )
 *   ),
 *   @SWG\Response(
 *     response="500",
 *     description="Internal server error",
 *     @SWG\Schema(
 *       @SWG\Property(property="error", type="string", description="Message error", example="Internal server error"),
 *       @SWG\Property(property="code", type="integer", description="Response code", example="500"),
 *     ),
 *   ),
 *   @SWG\Tag(
 *     name="Authentication",
 *   ),
 *   @SWG\Tag(
 *     name="Password",
 *   ),
 *   @SWG\Tag(
 *     name="Users",
 *   ),
 *   @SWG\Tag(
 *     name="User Transactions",
 *   ),
 *   @SWG\Tag(
 *     name="Carts",
 *   ),
 *   @SWG\Tag(
 *     name="Cart Lotteries",
 *   ),
 *   @SWG\Tag(
 *     name="Cart Syndicates",
 *   ),
 *   @SWG\Tag(
 *     name="Cart Scratch Cards",
 *   ),
 *   @SWG\Tag(
 *     name="Cart Live Lotteries",
 *   ),
 *   @SWG\Tag(
 *     name="Cart Raffles",
 *   ),
 *   @SWG\Tag(
 *     name="Cart Raffles Syndicates",
 *   ),
 *   @SWG\Tag(
 *     name="Deals",
 *   ),
 *   @SWG\Tag(
 *     name="Subscriptions",
 *   ),
 *   @SWG\Tag(
 *     name="Lotteries",
 *   ),
 *   @SWG\Tag(
 *     name="Syndicates",
 *   ),
 *   @SWG\Tag(
 *     name="Memberships",
 *   ),
 *   @SWG\Tag(
 *     name="Games",
 *   ),
 *   @SWG\Tag(
 *     name="Scratch Cards",
 *   ),
 *   @SWG\Tag(
 *     name="Live Lotteries",
 *   ),
 *   @SWG\Tag(
 *     name="Raffles",
 *   ),
 *   @SWG\Tag(
 *     name="Raffles Syndicates",
 *   ),
 *   @SWG\Tag(
 *     name="Reports",
 *   ),
 *   @SWG\Tag(
 *     name="Location",
 *   ),
 *   @SWG\Tag(
 *     name="Payways",
 *   ),
 * )
 */
    /**
     * @SWG\Post(
     *   path="/oauth/token",
     *   summary="Request access token",
     *   tags={"Authentication"},
     *   consumes={"multipart/form-data"},
     *   @SWG\Parameter(
     *     name="client_id",
     *     in="formData",
     *     description="Client Id",
     *     type="integer",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="client_secret",
     *     in="formData",
     *     description="Client Secret",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="grant_type",
     *     in="formData",
     *     description="Authentication type (password || client_credentials || refresh_token)",
     *     type="string",
     *     required=true,
     *   ),
     *   @SWG\Parameter(
     *     name="username",
     *     in="formData",
     *     description="User to authenticate (email)",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="formData",
     *     description="User password",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="refresh_token",
     *     in="formData",
     *     description="Token to refresh",
     *     type="string",
     *     required=false,
     *   ),
     *   @SWG\Parameter(
     *     name="pcbl",
     *     in="formData",
     *     description="free spin login",
     *     type="string",
     *     required=false,
     *   ),
     *   security={
     *     {"user_ip":{},  "Content-Language":{}}
     *   },
     *   @SWG\Response(
     *     response=200,
     *     description="Successful operation",
     *     @SWG\Schema(
     *       @SWG\Property(property="token_type", type="string", description="Token type", example="Bearer"),
     *       @SWG\Property(property="expires_in", type="integer", description="Time to expire token", example="31536000"),
     *       @SWG\Property(property="access_token", type="string", description="Access token", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjBkNmUxZjE4YjFhZDY0ZWRjOGE3YzZmYmI3MjA2NzhjMjVjNmIyYmU5N2I2YTZjZWIxMzNiZjJkMzYxNTE3NmU1ZDIyODA2NGE2MTdmNTE4In0.eyJhdWQiOiIxIiwianRpIjoiMGQ2ZTFmMThiMWFkNjRlZGM4YTdjNmZiYjcyMDY3OGMyNWM2YjJiZTk3YjZhNmNlYjEzM2JmMmQzNjE1MTc2ZTVkMjI4MDY0YTYxN2Y1MTgiLCJpYXQiOjE1Mjc1MTY1MTUsIm5iZiI6MTUyNzUxNjUxNSwiZXhwIjoxNTU5MDUyNTE1LCJzdWIiOiIiLCJzY29wZXMiOltdfQ.cuZWEPbctRl_AtAhw599zQAYokP-0OR8_Urgu13wtL7PhX2aK2e5UwZZAOTwhrP4CHdDRFNw40sh3gUaA5mw7_txUvlVu_XBvjy8KfqCznRUSXXaDwIrOKahG3p7FiSTmlDbSK_YaNUTGDApzpMetUgB2NsBpv6CI0HNj3hCEGHKRo51EBRMrLugCe2vOE3Z51wiOKvZHtTcfDnBQgpSbDi8gHu-quDEwJIffk8gkICrj6NHvowKP-Ys1bMvB3WLHd_jTIOro0yUfzNObyg-gP7oWVIzhsth4E0u8Hv0OdrRevAXPzA8zVkeOnjDSHIpWHNJvdrwcgJqGex7Z8PccZmks20qVNC2fEzr8wRn-akEz7kTO0uuYL3X9bwBEoWIsHQobqa4OGigCF-gl6-bEVqeHJ54HbFZRNVP5URva5iw-UMWEA3m825hAk8lAYPq5QZoEKCJaSZl9Jlm4erWWyXoGMbT2-rvRZF12rxe87pNZJLd0pgIHtCFeJhbvflfvgc3hQp57tqTjR9NB8Y1zE4ag4MvMp1p4YrzvlgAPYqUzc9LopiuoWxil6QnPMN-S4EknrRjaxMNamv_s8rgK56AUrb3TQYyIzri01KOrgZ9yEy_Kbsea2zD1I_vzNWdxxK4GZoAKT6I2_qWMn_A4dF_JLCiyX-qeHW86exXSkQ"),
     *     ),
     *   ),
     *   @SWG\Response(response=401, ref="#/responses/401"),
     *   @SWG\Response(response=422, ref="#/responses/422"),
     *   @SWG\Response(response=500, ref="#/responses/500"),
     * )
     *
     */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
