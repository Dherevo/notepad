<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;

class ApiController extends Controller {

    /**
     * Display a listing of the resource.
     */
    public function index() {
        $notes = Note::all();

        if (!$notes) {
            return response()->json([
                'message' => 'Currently there are no notes yet.'
            ]);
        }

        return response()->json($notes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {

        $request->validate([
            'title' => 'required|max:255',
            'body' =>  'required',
        ]);



        $note = new Note;
        $note->title = $request->title;
        $note->body = $request->body;
        $note->save();

        return response()->json(['message' => 'Note created successfully']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id) {
        $note = Note::find($id);

        if (!$note) {
            return response()->json([
                'message' => 'Note not found'
            ]);
        }

        return response()->json($note);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $note = Note::find($id);

        if (!$note) {
            return response()->json([
                'message' => 'Note not found'
            ]);
        }

        $validated = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
        ]);

        $note->title = $request->title;
        $note->body = $request->body;
        $note->save();

        return response()->json(['message' => 'Note updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {
        $note = Note::find($id);

        if (!$note) {
            return response()->json([
                'message' => 'Note not found'
            ]);
        }

        $note->delete();

        return response()->json(['message' => 'Note deleted successfully', 'note' => $note]);
    }
}
