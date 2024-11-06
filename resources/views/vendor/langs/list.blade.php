@extends(config('elseyyid-location.layout'))
@section(config('elseyyid-location.content_section'))
        <div class="col-md-12">

            @php $lang_region = LaravelLocalization::getSupportedLocales()[str_replace('_','-',$lang)]['regional']; @endphp
            @php $lang_native = LaravelLocalization::getSupportedLocales()[str_replace('_','-',$lang)]['native']; @endphp
            <h6 class="font-weight-semibold fs-16 text-center mb-4">{{ __('Editing Language') }}: {{ ucfirst($lang_native) }} <span class="text-muted fs-12 ml-1">{{ucfirst($lang)}}</span></h6>

            <input class="form-control mb-6 mt-4 text-muted fs-12 font-weight-semibold" id="search_string"  type="text" onkeyup="searchStrings()" placeholder="{{ __('Filter strings...') }}">

            <div class="card language-editor-box">
                <div class="card-table table-responsive">
    
                    <table class="table" id="strings">
                        <thead>
                            <tr>
                                <th class="text-muted fs-12 font-weight-semibold pl-4">{{ __('Strings') }}</th>
                                <th class="text-muted fs-12 font-weight-semibold">{{ __('Translations') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($list as $key => $value)
                                <tr>
                                    <td class="hidden" width="10px">
                                        <input type="checkbox" name="ids_to_edit[]" value="{{ $value->id }}"/>
                                    </td>
                                    @foreach ($value->toArray() as $key => $element)
                                        @if ($key !== 'code')
                                            @if ($key === 'en')
                                                <td class="w-50 pl-4" style="vertical-align: middle">
                                                    <div data-name="{{ $key }}">{{ $element }}</div>
                                                </td>
                                            @else
                                                <td class="w-50">
                                                    <input class="form-control w-100" data-pk="{{ $value->code }}" data-name="{{ $key }}" type="text" value="{{ $element }}" placeholder="{{ __('enter translation') }}">
                                                </td>
                                            @endif
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="fixed-bottom text-center pl-9 ml-9">
                    <div class="btn btn-primary pl-9 pr-9 mb-5" id="save_strings" data-lang="{{ $lang }}">{{ __('Save') }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        let loading = `<span class="loading">
						<span style="background-color: #fff;"></span>
						<span style="background-color: #fff;"></span>
						<span style="background-color: #fff;"></span>
						</span>`;
        $(document).ready(function() {
       
            $('#save_strings').click(function() {

                document.getElementById("save_strings").disabled = true;
                document.getElementById("save_strings").innerHTML = loading;
                document.querySelector('#loader-line')?.classList?.remove('hidden'); 

                var formData = new FormData();
                var inputData = [];

                $('table input[type="text"]').each(function() {
                    var value = $(this).val();
                    inputData.push(value);
                });

                var jsonData = JSON.stringify(inputData);
                formData.append('data', jsonData);
                formData.append('lang', $(this).data('lang'));

                $.ajax({
                    type: "post",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    },
                    url: "/app/admin/settings/languages/lang/update-all",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        toastr.success("{{ __('Strings saved successfully') }}");
                        document.getElementById("save_strings").disabled = false;
                        document.getElementById("save_strings").innerHTML = "{{ __('Save') }}";
                        document.querySelector('#loader-line')?.classList?.add('hidden');
                    },
                    error: function(data) {
                        var err = data.responseJSON.errors;
                        $.each(err, function(index, value) {
                            toastr.error(value);
                        });
                        document.getElementById("save_strings").disabled = false;
                        document.getElementById("save_strings").innerHTML = "{{ __('Save') }}";
                        document.querySelector('#loader-line')?.classList?.add('hidden');
                    }
                });
                return false;
            });
        });

        function searchStrings() {

            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("search_string");
            filter = input.value.toUpperCase();
            table = document.getElementById("strings");
            tr = table.getElementsByTagName("tr");

            for (i = 0; i < tr.length; i++) {
                var foundMatch = false;
                td = tr[i].getElementsByTagName("td");

                if (td.length > 0) {
                    for (j = 0; j < td.length; j++) {
                        var divElement = td[j].querySelector("div[data-name='en']");
                        var inputElement = td[j].querySelector("input");
                        if (divElement && divElement.textContent.toUpperCase().indexOf(filter) > -1) {
                            foundMatch = true;
                            break;
                        } else if (inputElement && inputElement.value.toUpperCase().indexOf(filter) > -1) {
                            foundMatch = true;
                            break;
                        }
                    }
                }

                if (foundMatch) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = tr[i].parentNode.tagName === 'THEAD' ? 'table-row' : 'none';
                }
            }
        }
    </script>
@endsection
