@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Plans</h4>
            <button class="btn btn-primary" onclick="openPlanModal()">+ New Plan</button>
        </div>

        <div class="card">
            <div class="card-body table-responsive">
                <table id="plansTable" class="table table-striped table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Bandwidth (Mbps)</th>
                            <th>Price (Rp, inc PPN)</th>
                            <th>Active</th>
                            <th style="width:140px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $i => $p)
                            <tr data-id="{{ $p->id }}">
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $p->name }}</td>
                                <td>{{ $p->bandwidth_mbps }}</td>
                                <td>{{ number_format($p->price_inc_ppn, 0, ',', '.') }}</td>
                                <td>
                                    @if($p->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary"
                                        onclick='openPlanModal(@json($p))'>Edit</button>
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="deletePlan({{ $p->id }})">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="planModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="planForm" class="modal-content">
                @csrf
                <input type="hidden" id="plan_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="planModalTitle">New Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Name</label>
                        <input class="form-control" id="name" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Bandwidth (Mbps)</label>
                        <input type="number" min="1" class="form-control" id="bandwidth_mbps" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Price inc. PPN (Rp)</label>
                        <input type="number" min="0" class="form-control" id="price_inc_ppn" required>
                    </div>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(function () { $('#plansTable').DataTable(); });

        function openPlanModal(plan = null) {
            $('#planForm')[0].reset();
            $('#plan_id').val(plan ? plan.id : '');
            $('#planModalTitle').text(plan ? 'Edit Plan' : 'New Plan');
            if (plan) {
                $('#name').val(plan.name);
                $('#bandwidth_mbps').val(plan.bandwidth_mbps ?? '');
                $('#price_inc_ppn').val(plan.price_inc_ppn ?? 0);
                $('#is_active').prop('checked', !!plan.is_active);
            } else {
                $('#is_active').prop('checked', true);
            }
            new bootstrap.Modal(document.getElementById('planModal')).show();
        }

        $('#planForm').on('submit', async function (e) {
            e.preventDefault();
            const id = $('#plan_id').val();
            const payload = {
                name: $('#name').val(),
                bandwidth_mbps: parseInt($('#bandwidth_mbps').val() || 0, 10),
                price_inc_ppn: parseInt($('#price_inc_ppn').val() || 0, 10),
                is_active: $('#is_active').is(':checked') ? 1 : 0,
                _token: '{{ csrf_token() }}'
            };
            const url = id ? `{{ url('plans') }}/${id}` : `{{ url('plans') }}`;
            const method = id ? 'PUT' : 'POST';
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify(payload)
            });
            const json = await res.json();
            if (json.ok) { location.reload(); } else { alert(json.message ?? 'Save failed'); }
        });

        async function deletePlan(id) {
            if (!confirm('Delete this plan?')) return;
            const res = await fetch(`{{ url('plans') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
            const json = await res.json();
            if (json.ok) { location.reload(); } else { alert(json.message ?? 'Delete failed'); }
        }
    </script>
@endsection