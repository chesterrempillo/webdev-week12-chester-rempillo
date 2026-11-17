<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use Illuminate\Http\Request;

class BookController extends Controller
{
    // Display all books belonging to the authenticated user.
    public function index(Request $request)
    {
        $books = $request->user()
            ->books()
            ->latest()
            ->paginate(10);

        return BookResource::collection($books);
    }

    // Display one book belonging to the authenticated user.
    public function show(Request $request, string $id)
    {
        $book = $request->user()
            ->books()
            ->findOrFail($id);

        return new BookResource($book);
    }

    // Create a new book.
    public function store(StoreBookRequest $request)
    {
        $book = $request->user()
            ->books()
            ->create($request->validated());

        return (new BookResource($book))
            ->additional([
                'success' => true,
                'message' => 'Book created successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    // Update an existing book.
    public function update(UpdateBookRequest $request, string $id)
    {
        $book = $request->user()
            ->books()
            ->findOrFail($id);

        $book->update($request->validated());

        return (new BookResource($book->fresh()))
            ->additional([
                'success' => true,
                'message' => 'Book updated successfully.',
            ]);
    }

    // Delete a book.
    public function destroy(Request $request, string $id)
    {
        $book = $request->user()
            ->books()
            ->findOrFail($id);

        $book->delete();

        return response()->json([
            'success' => true,
            'message' => 'Book deleted successfully.',
        ]);
    }
}
