<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrainingCategory;

class TrainingCategoryController extends Controller
{
    public function index()
    {
        $categories = TrainingCategory::ordered()->get();
        return view('training.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:training_categories,name',
            'description' => 'nullable|string|max:1000',
            'validity_months' => 'nullable|integer|min:1|max:240',
            'is_first_aid' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Name ist erforderlich.',
            'name.unique' => 'Eine Kategorie mit diesem Namen existiert bereits.',
            'validity_months.integer' => 'Gültigkeitsdauer muss eine ganze Zahl sein.',
            'validity_months.min' => 'Gültigkeitsdauer muss mindestens 1 Monat sein.',
        ]);

        TrainingCategory::create([
            'name' => $request->name,
            'description' => $request->description,
            'validity_months' => $request->validity_months,
            'is_first_aid' => $request->boolean('is_first_aid'),
            'is_active' => true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('training.categories.index')->with('success', 'Schulungskategorie wurde erfolgreich angelegt.');
    }

    public function update(Request $request, TrainingCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:training_categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
            'validity_months' => 'nullable|integer|min:1|max:240',
            'is_first_aid' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Name ist erforderlich.',
            'name.unique' => 'Eine Kategorie mit diesem Namen existiert bereits.',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'validity_months' => $request->validity_months,
            'is_first_aid' => $request->boolean('is_first_aid'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('training.categories.index')->with('success', 'Schulungskategorie wurde erfolgreich aktualisiert.');
    }

    public function destroy(TrainingCategory $category)
    {
        if ($category->completions()->count() > 0) {
            return redirect()->route('training.categories.index')
                ->with('error', 'Diese Kategorie kann nicht gelöscht werden, da Schulungseinträge vorhanden sind.');
        }

        $category->delete();
        return redirect()->route('training.categories.index')->with('success', 'Schulungskategorie wurde gelöscht.');
    }
}
