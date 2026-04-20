@if(session('success'))
    <script>
        Swal.fire({
            title: 'Success!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'OK'
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            title: 'Error!',
            text: "{{ session('error') }}",
            icon: 'error',
            confirmButtonText: 'OK'
        });
    </script>
@endif

@if ($errors->any())
    <script>
        Swal.fire({
            title: 'Validation Error!',
            html: `{!! implode('<br>', $errors->all()) !!}`,
            icon: 'error'
        });
    </script>
@endif