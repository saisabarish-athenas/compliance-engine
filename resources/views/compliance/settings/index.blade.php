<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statutory Settings - Compliance Engine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Statutory Establishment Settings</h4>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('compliance.settings.update') }}">
                            @csrf

                            <h5 class="border-bottom pb-2 mb-3">Establishment Details</h5>

                            <div class="mb-3">
                                <label for="establishment_name" class="form-label">Establishment Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="establishment_name" name="establishment_name" 
                                       value="{{ old('establishment_name', $tenant->establishment_name ?? '') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="factory_license_no" class="form-label">Factory License Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="factory_license_no" name="factory_license_no" 
                                       value="{{ old('factory_license_no', $tenant->factory_license_no ?? '') }}" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="pf_code" class="form-label">PF Code</label>
                                    <input type="text" class="form-control" id="pf_code" name="pf_code" 
                                           value="{{ old('pf_code', $tenant->pf_code ?? '') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="esi_code" class="form-label">ESI Code</label>
                                    <input type="text" class="form-control" id="esi_code" name="esi_code" 
                                           value="{{ old('esi_code', $tenant->esi_code ?? '') }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="labour_office_address" class="form-label">Labour Office Address</label>
                                <textarea class="form-control" id="labour_office_address" name="labour_office_address" rows="2">{{ old('labour_office_address', $tenant->labour_office_address ?? '') }}</textarea>
                            </div>

                            <h5 class="border-bottom pb-2 mb-3 mt-4">Branch/Unit Details</h5>

                            @foreach($branches as $index => $branch)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-title">Branch {{ $index + 1 }}</h6>
                                        
                                        <input type="hidden" name="branches[{{ $index }}][id]" value="{{ $branch->id }}">

                                        <div class="mb-3">
                                            <label for="branch_unit_name_{{ $index }}" class="form-label">Unit Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="branch_unit_name_{{ $index }}" 
                                                   name="branches[{{ $index }}][unit_name]" 
                                                   value="{{ old('branches.'.$index.'.unit_name', $branch->unit_name ?? $branch->branch_name ?? '') }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="branch_address_{{ $index }}" class="form-label">Address <span class="text-danger">*</span></label>
                                            <textarea class="form-control" id="branch_address_{{ $index }}" 
                                                      name="branches[{{ $index }}][address]" rows="3" required>{{ old('branches.'.$index.'.address', $branch->address ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('compliance.dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
