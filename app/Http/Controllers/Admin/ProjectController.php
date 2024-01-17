<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::all();

        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $technologies = config('technologies.key');

        return view('admin.projects.create', compact('categories', 'technologies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $categories = Category::all();
        $formData = $request->validated();
        $slug = Str::slug($formData['title'], '-');
        $formData['slug'] = $slug;
        $user_id = Auth::id();
        $formData['user_id'] = $user_id;

        if ($request->hasFile('img')) {
            $path = Storage::put('uploads', $request->file('img'));
            $formData['img'] = $path;
        }
        if ($request->input('technologies')) {
            $formData['technologies'] = implode(',', $request->input('technologies'));
        }
        $project = Project::create($formData);
        if ($project->has('technologies')) {
            $project->technologies()->attach($request->technologies);
        }

        return redirect()->route('admin.projects.index', $project->slug);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $project->technologies = explode(',', $project->technologies);

        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $categories = Category::all();
        $technologies = config('technologies.key');

        return view('admin.projects.edit', compact('project', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        $formData = $request->validated();
        $slug = Str::slug($formData['title'], '-');
        $formData['slug'] = $slug;
        $formData['user_id'] = $project->user_id;

        if ($request->hasFile('img')) {
            if ($project->img) {
                Storage::delete($project->img);
            }
            $path = Storage::put('uploads', $request->file('img'));
            $formData['img'] = $path;
        }
        if ($request->input('technologies')) {
            $formData['technologies'] = implode(',', $request->input('technologies'));
        } else {
            $formData['technologies'] = '';
        }
        $project->update($formData);

        return to_route('admin.projects.show', $project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        if ($project->img) {
            Storage::delete($project->img);
        }

        $project->delete();

        return to_route('admin.projects.index')->with('message', "Project $project->title deleted");
    }
}
