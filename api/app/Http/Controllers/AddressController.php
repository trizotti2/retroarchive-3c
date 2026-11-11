<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Resources\AddressResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class AddressController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return AddressResource::collection($request->user()->addresses()->latest('id')->get());
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $address = $request->user()->addresses()->create($request->validated());

        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    public function update(StoreAddressRequest $request, string $id): AddressResource
    {
        $address = $request->user()->addresses()->findOrFail($this->positiveId($id));
        $address->update($request->validated());

        return new AddressResource($address);
    }

    public function destroy(Request $request, string $id): Response
    {
        $request->user()->addresses()->findOrFail($this->positiveId($id))->delete();

        return response()->noContent();
    }
}
