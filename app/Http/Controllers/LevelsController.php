<?php

namespace App\Http\Controllers;

use App\Models\Checkpoint;
use App\Models\Level;
use App\Models\Sublevel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LevelsController extends Controller
{
    public function getLevels(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            // $levels = Level::with('sublevels')->get();
            $levels = Level::withCount('sublevels')->get();
            return response()->json(['levels' => $levels],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getLevelsTree(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $levels = Level::with('sublevels')->get();
            $levelsTree = [];

            foreach ($levels as $level) {
                $formattedLevel = [
                    'key' => $level->id,
                    'label' => $level->level_name,
                    'icon' => 'pi pi-fw pi-star',
                    'children' => [],
                ];

                foreach ($level->sublevels as $sublevel) {
                    $formattedSublevel = [
                        'key' => $level->id . '-' . $sublevel->id,
                        'label' => $sublevel->sublevel_name,
                        'icon' => 'pi pi-fw pi-bookmark-fill',
                    ];

                    $formattedLevel['children'][] = $formattedSublevel;
                }

                $levelsTree[] = $formattedLevel;
            }

            return response()->json(['levelsTree' => $levelsTree],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function getLevel(Request $request , $id){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $level = Level::with('sublevels.checkpoints')->find($id);
            return response()->json(['level' => $level],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }
    public function createLevel(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            {
                // Validate the request data
                $request->validate([
                    'level_name' => 'required|unique:levels,level_name',
                    'level_description' => 'nullable|string',
                    'sublevels' => 'required|array|min:1',
                    'sublevels.*.sublevel_name' => 'required|unique:sublevels,sublevel_name',
                    'sublevels.*.checkpoints' => 'required|array|min:1',
                    'sublevels.*.checkpoints.*.checkpoint_name' => 'required',
                    'sublevels.*.checkpoints.*.checkpoint_description' => 'nullable|string',
                ]);

                // Create the level
                $level = Level::create([
                    'level_name' => $request->input('level_name'),
                    'level_description' => $request->input('level_description'),
                ]);

                // Create sublevels and checkpoints
                foreach ($request->input('sublevels') as $sublevelData) {
                    $sublevel = $level->sublevels()->create([
                        'sublevel_name' => $sublevelData['sublevel_name'],
                    ]);

                    foreach ($sublevelData['checkpoints'] as $checkpointData) {
                        $sublevel->checkpoints()->create([
                            'checkpoint_name' => $checkpointData['checkpoint_name'],
                            'checkpoint_description' => isset($checkpointData['checkpoint_description']) ? $checkpointData['checkpoint_description'] : null,
                        ]);
                    }
                }
                // Return a response indicating success
                return response()->json(['message' => 'Level created successfully'], 201);
            }
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }

    public function updateLevel(Request $request , $id){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            // Validate the request data
            $request->validate([
                'level_name' => [
                    Rule::unique('levels' , 'level_name')->ignore($id)
                ],
                'level_description' => 'nullable|string',
                'sublevels' => 'required|array|min:1',
                'sublevels.*.id' => 'numeric|nullable',
                'sublevels.*.sublevel_name' => 'required',
                'sublevels.*.checkpoints' => 'required|array|min:1',
                'sublevels.*.checkpoints.*.id' => 'numeric|nullable',
                'sublevels.*.checkpoints.*.checkpoint_name' => 'required',
                'sublevels.*.checkpoints.*.checkpoint_description' => 'nullable|string',
            ]);

            $level = Level::find($id);
            // Update the level
            $level->update([
                'level_name' => $request->input('level_name'),
                'level_description' => $request->input('level_description'),
            ]);

            // Update sublevels and checkpoints
            foreach ($request->input('sublevels') as $sublevelData) {
                if(isset($sublevelData['id'])){
                    $sublevel = Sublevel::findOrFail($sublevelData['id']);
                    $sublevel->update([
                        'sublevel_name' => $sublevelData['sublevel_name'],
                    ]);
                }
                else{
                    Sublevel::create([
                        'level_id' => $id,
                        'sublevel_name' => $sublevelData['sublevel_name'],
                    ]);
                }

            foreach ($sublevelData['checkpoints'] as $checkpointData) {
                if(isset($checkpointData['id'])){
                    $checkpoint = Checkpoint::findOrFail($checkpointData['id']);
                    $checkpoint->update([
                        'checkpoint_name' => $checkpointData['checkpoint_name'],
                        'checkpoint_description' => isset($checkpointData['checkpoint_description']) ? $checkpointData['checkpoint_description'] : null,
                    ]);
                }
                else{
                    Checkpoint::create([
                        'sublevel_id' => $sublevelData['id'],
                        'checkpoint_name' => $checkpointData['checkpoint_name'],
                        'checkpoint_description' => isset($checkpointData['checkpoint_description']) ? $checkpointData['checkpoint_description'] : null,
                    ]);
                }
            }
        }
        // Return a response indicating success
        return response()->json(['message' => 'Level updated successfully'], 200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
}

    public function levelsBulkDelete(Request $request){
        $user = $request->user();
        if($user->type == 'admin' || $user->type == 'Employee'){
            $validatedData = $request->validate([
                'levels_ids' => 'required|array',
            ]);
            $levelIds = $validatedData['levels_ids']; // Array of IDs to be deleted
            foreach ($levelIds as $id) {
                $level = Level::findOrFail($id);
                $sublevels = $level->sublevels;

                // Update associated customers' level and sublevel to null
                $level->customers()->update(['level_id' => null]);
                $sublevels->each(function ($sublevel) {
                    $sublevel->customers()->update(['sublevel_id' => null]);
                    $sublevel->checkpoints()->delete();
                });

                // Delete the level and sublevels
                $level->delete();
                $sublevels->each->delete();
            }
            return response()->json(['message' => 'deleted successfully'],200);
        }
        else{
            return response()->json(['message' => 'unauthorized'],401);
        }
    }


}
