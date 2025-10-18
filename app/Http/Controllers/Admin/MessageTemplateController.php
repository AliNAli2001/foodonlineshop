<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    /**
     * Show all message templates.
     */
    public function index()
    {
        $templates = MessageTemplate::paginate(15);
        return view('admin.messages.index', compact('templates'));
    }

    /**
     * Show create template form.
     */
    public function create()
    {
        return view('admin.messages.create');
    }

    /**
     * Store a new template.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:100',
            'message_type' => 'required|in:whatsapp,email',
            'content' => 'required|string',
            'language' => 'required|in:ar,en',
        ]);

        MessageTemplate::create($validated);

        return redirect()->route('admin.messages.index')
            ->with('success', 'Message template created successfully.');
    }

    /**
     * Show edit template form.
     */
    public function edit($templateId)
    {
        $template = MessageTemplate::findOrFail($templateId);
        return view('admin.messages.edit', compact('template'));
    }

    /**
     * Update template.
     */
    public function update(Request $request, $templateId)
    {
        $template = MessageTemplate::findOrFail($templateId);

        $validated = $request->validate([
            'template_name' => 'required|string|max:100',
            'message_type' => 'required|in:whatsapp,email',
            'content' => 'required|string',
            'language' => 'required|in:ar,en',
        ]);

        $template->update($validated);

        return redirect()->route('admin.messages.index')
            ->with('success', 'Message template updated successfully.');
    }

    /**
     * Delete template.
     */
    public function destroy($templateId)
    {
        $template = MessageTemplate::findOrFail($templateId);
        $template->delete();

        return redirect()->route('admin.messages.index')
            ->with('success', 'Message template deleted successfully.');
    }
}

