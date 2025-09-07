@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Sites</h4>
            <button class="btn btn-primary" onclick="openSiteModal()">+ New Site</button>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table id="sitesTable" class="table table-striped table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Site Name</th>
                            <th>Code</th>
                            <th>Address</th>
                            <th style="width:140px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sites as $i => $s)
                            <tr data-id="{{ $s->id }}">
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $s->name }}</td>
                                <td>{{ $s->code }}</td>
                                <td>{{ $s->address }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary"
                                        onclick='openSiteModal(@json($s))'>Edit</button>
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="deleteSite({{ $s->id }})">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="siteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="siteForm" class="modal-content">
                @csrf
                <input type="hidden" id="site_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="siteModalTitle">New Site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Site Name</label>
                            <input class="form-control" id="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Code</label>
                            <input class="form-control" id="code" required placeholder="Unique code">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" id="address" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(function () { $('#sitesTable').DataTable(); });

        function openSiteModal(site = null) {
            $('#siteForm')[0].reset();
            $('#site_id').val(site ? site.id : '');
            $('#siteModalTitle').text(site ? 'Edit Site' : 'New Site');
            if (site) {
                $('#name').val(site.name);
                $('#code').val(site.code);
                $('#address').val(site.address ?? '');
            }
            new bootstrap.Modal(document.getElementById('siteModal')).show();
        }

        $('#siteForm').on('submit', async function (e) {
            e.preventDefault();
            const id = $('#site_id').val();
            const payload = {
                name: $('#name').val(),
                code: $('#code').val(),
                address: $('#address').val(),
                _token: '{{ csrf_token() }}'
            };
            const url = id ? `{{ url('sites') }}/${id}` : `{{ url('sites') }}`;
            const method = id ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            if (json.ok) { location.reload(); } else { alert(json.message ?? 'Save failed'); }
        });

        async function deleteSite(id) {
            if (!confirm('Delete this site?')) return;
            const res = await fetch(`{{ url('sites') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const json = await res.json();
            if (json.ok) { location.reload(); } else { alert(json.message ?? 'Delete failed'); }
        }
    </script>
@endsection