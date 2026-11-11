<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Resources\AddressResource;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AddressController extends Controller
{
    public function __construct(private AddressService $addresses) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return AddressResource::collection($this->addresses->listForUser($request->user()));
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $this->addresses->create($request->user(), $request->validated());

        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    public function update(StoreAddressRequest $request, string $id): AddressResource
    {
        return new AddressResource(
            $this->addresses->update($request->user(), $this->positiveId($id), $request->validated()),
        );
    }

    public function destroy(Request $request, string $id): Response
    {
        $this->addresses->delete($request->user(), $this->positiveId($id));

        return response()->noContent();
    }
}
