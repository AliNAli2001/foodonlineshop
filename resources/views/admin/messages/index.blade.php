@extends('layouts.admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h1>Message Templates</h1>
        <a href="{{ route('admin.messages.create') }}" class="btn btn-primary">Add Template</a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($templates as $template)
                    <tr>
                        <td>{{ $template->id }}</td>
                        <td>{{ $template->name }}</td>
                        <td>{{ ucfirst($template->type) }}</td>
                        <td>
                            <a href="{{ route('admin.messages.edit', $template->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.messages.destroy', $template->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No templates found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="row mt-4">
            <div class="col-md-12">
                {{ $templates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

