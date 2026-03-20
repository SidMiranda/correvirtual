
    @if(session('success'))
        <div style="background: #1db954; color: white; padding: 15px; text-align: center; font-weight: bold;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #e74c3c; color: white; padding: 15px; text-align: center;">
            <ul style="margin: 0; padding-left: 20px; list-style-type: none;">
                @foreach($errors->all() as $error)
                    <li>⚠️ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
