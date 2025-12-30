@extends('crudPackage::layout.main',['activePage' => 'Media'])
@section('content')
    <div class="d-flex justify-content-md-end justify-content-center gap-4">
        <button type="button" class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#insertModal">
            <i class="bi bi-folder-plus fs-2"></i> Yeni Klasör
        </button>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#fileModal">
            <i class="bi bi-file-earmark-arrow-up fs-2"></i> Dosya Ekle
        </button>
    </div>
    <div class="card mt-5">
        <div class="card-body">
            <div class="d-flex flex-stack mb-5">
                <div class="badge badge-lg badge-light-primary">
                    <div class="d-flex align-items-center flex-wrap">
                        <a href="{{ route('media.index') }}" class="color-primary">Medya</a>
                        @if(count($segments) > 0)
                            @foreach($segments as $i => $segment)
                                <i class="ki-duotone ki-right fs-2 text-primary mx-1"></i>
                                @if((count($segments) - 1) == $i)
                                    <span class="color-secondary">{{ $segment }}</span>
                                @else
                                    <a href="{{ route('media.index', implode('/', array_slice($segments, 0, $i + 1))) }}" class="color-primary">{{ $segment }}</a>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="badge badge-lg badge-primary">
                    <span id="kt_file_manager_items_counter">{{ count($folders) + count($files) }} adet</span>
                </div>
            </div>
            <div class="row media align-items-start">
                <div class="col-lg-9 lists">
                    @foreach($folders as $folder)
                        @php
                            $folderName = isset($path) ? str_replace($path.'/','',$folder) :  $folder;
                        @endphp
                        <a href="javascript:void(0)" data-path="{{ $folder }}" data-url="{{ route('media.index',$folder) }}" data-name="{{ $folderName }}" class="item folder" data-bs-toggle="tooltip" data-bs-placement="top" title="{{$folderName}}">
                            <i class="bi bi-folder-fill"></i>
                            <span>{{ $folderName }}</span>
                        </a>
                    @endforeach

                    @foreach($files as $file)
                        @php
                            $size = null;

                            try
                            {
                                $size = Storage::disk($disk)->size($file);
                            }
                            catch (\Exception $e)
                            {
                                $size = null;
                            }

                            $extension = getExtension($file);
                            $url       = '/upload/'.$file;
                            $isImage   = in_array($extension, ['jpg','jpeg','png','webp','gif']);
                            $modified   = Storage::disk($disk)->lastModified($file);
                            $fileName   = isset($path) ? str_replace($path.'/','',$file) :  $file;
                        @endphp
                        <a href="javascript:void(0)" class="item file" data-bs-toggle="tooltip" data-bs-placement="top" title="{{$fileName}}" data-name="{{ $fileName }}"
                           data-url="{{ $url }}"
                           data-ext="{{ $extension }}"
                           data-size="{{ $size }}"
                           data-modified="{{ date('d.m.Y H:i', $modified) }}"
                           data-image="{{ $isImage ? '1' : '0' }}">

                            <i class="bi bi-filetype-{{getExtension($file)}}"></i>
                            <span>{{ shortFilename($fileName) }}</span>
                        </a>
                    @endforeach
                </div>

                <div id="preview-panel" class="preview col-lg-3">
                    <div class="empty">
                        <img src="{{ asset('crud/images/empty.png') }}">
                    </div>

                    <div class="content d-none">
                        <img id="preview-image" class="img-fluid d-none">
                        <div class="icon" id="preview-icon-selector">
                            <i id="preview-icon" class="bi"></i>
                        </div>
                        <hr>

                        <ul class="list-unstyled small">
                            <li><strong>Ad:</strong> <span id="p-name"></span></li>
                            <li><strong>Boyut:</strong> <span id="p-size"></span></li>
                            <li><strong>Tarih:</strong> <span id="p-modified"></span></li>
                            <li>
                                <strong>URL:</strong>
                                <a id="p-url" class="text-primary" target="_blank">Yeni Sekmede Aç</a>
                                <button class="copy" data-clipboard-target="#p-url"> <i class="bi bi-copy fs-4"></i> </button>
                            </li>
                            <li>
                                <button id="p-delete" class="btn btn-sm btn-danger w-100" onclick="destroy(this)" data-route="{{ route('media.delete',['path' => $path]) }}" data-title=""> </button>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade modal-xl" data-bs-backdrop="static" data-bs-keyboard="false" id="insertModal"
         aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-header" id="kt_modal_add_user_header">
                    <h2 class="fw-bold">Yeni Klasör</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span
                                    class="path2"></span></i>
                    </div>
                </div>

                <div class="modal-body scroll-y mx-5 mx-xl-10">
                    <form id="addUpdateForm" class="form  fv-plugins-bootstrap5 fv-plugins-framework"
                          method="post" action="{{route('media.createFolder')}}">
                        <div class="row scroll-y me-n7 pe-7" id="kt_modal_add_user_scroll"
                             data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                             data-kt-scroll-max-height="auto"
                             data-kt-scroll-dependencies="#kt_modal_add_user_header"
                             data-kt-scroll-wrappers="#kt_modal_add_user_scroll"
                             data-kt-scroll-offset="300px" style="max-height: 281px;">
                            <div class="form-group col-12 mb-7 fv-plugins-icon-container">
                                <label class="required fw-semibold fs-6 mb-2">Klasör Adı</label>
                                <input type="text" name="name"
                                       class="form-control form-control-solid mb-3 mb-lg-0"
                                       placeholder="Klasör Adı" onkeyup="slugify(this.value,this)">

                                <input type="hidden" name="path" value="{{ $path }}">
                            </div>
                        </div>

                        <div class="text-center border-top pt-10 mt-5">
                            <button type="reset" class="btn btn-light me-3" data-dismiss="modal"> Vazgeç
                            </button>
                            <button type="submit" class="btn btn-primary buttonForm"
                                    data-kt-users-modal-action="submit"> Kaydet
                            </button>
                            @include('crudPackage::components.loading')
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade modal-xl" data-bs-backdrop="static" data-bs-keyboard="false" id="fileModal"
         aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-dialog-centered ">
            <div class="modal-content">
                <div class="modal-header" id="kt_modal_add_user_header">
                    <h2 class="fw-bold">Dosya EKle</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span
                                    class="path2"></span></i>
                    </div>
                </div>

                <div class="modal-body scroll-y mx-5 mx-xl-10">
                    <form id="mediaUploadForm" class="form  fv-plugins-bootstrap5 fv-plugins-framework"
                          method="post" action="{{route('media.upload')}}">
                        <div class="row scroll-y me-n7 pe-7" id="kt_modal_add_user_scroll"
                             data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                             data-kt-scroll-max-height="auto"
                             data-kt-scroll-dependencies="#kt_modal_add_user_header"
                             data-kt-scroll-wrappers="#kt_modal_add_user_scroll"
                             data-kt-scroll-offset="300px" style="max-height: 281px;">
                            <div class="form-group col-12 mb-7 fv-plugins-icon-container">
                                <label class="required fw-semibold fs-6 mb-2">Dosyaları Seç</label>
                                <input type="file" name="files[]"
                                       class="form-control form-control-solid mb-3 mb-lg-0" multiple>

                                <input type="hidden" name="path" value="{{ $path }}">
                            </div>
                        </div>

                        <div class="text-center border-top pt-10 mt-5">
                            <button type="reset" class="btn btn-light me-3" data-dismiss="modal"> Vazgeç
                            </button>
                            <button type="submit" class="btn btn-primary buttonForm"
                                    data-kt-users-modal-action="submit"> Kaydet
                            </button>
                            @include('crudPackage::components.loading')
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        document.querySelectorAll('.item.file').forEach(item =>
        {
            item.addEventListener('click', function ()
            {
                document.querySelectorAll('.item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                $('#p-url').closest('li').removeClass('d-none');

                const panel = document.getElementById('preview-panel');

                panel.querySelector('.empty').classList.add('d-none');
                panel.querySelector('.content').classList.remove('d-none');

                const name    = this.dataset.name;
                const url     = this.dataset.url;
                const ext     = this.dataset.ext;
                const size    = this.dataset.size;
                const modified = this.dataset.modified;
                const isImage = this.dataset.image === '1';

                document.getElementById('p-delete').innerHTML     = '<i class="bi bi-trash fs-4"></i> Dosyayı Sil';
                document.getElementById('p-name').innerText       = name;
                document.getElementById('p-size').innerText       = formatSize(size);
                document.getElementById('p-modified').innerText    = modified;
                document.getElementById('p-url').href             = url;
                document.getElementById('p-delete').dataset.title = name + ' isimli dosyayı';
                document.getElementById('p-delete').dataset.route = document.getElementById('p-delete').dataset.route + '/' + name;

                const img  = document.getElementById('preview-image');
                const icon = document.getElementById('preview-icon-selector');

                if (isImage)
                {
                    img.src = url;
                    img.classList.remove('d-none');
                    icon.classList.add('d-none');
                }
                else
                {
                    icon.getElementsByTagName('i')[0].className = 'bi bi-filetype-' + ext;
                    icon.classList.remove('d-none');
                    img.classList.add('d-none');
                }
            });
        });

        function formatSize(bytes)
        {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(2) + ' MB';
        }

        const target = document.getElementById('p-url');
        const button = target.nextElementSibling;

        clipboard = new ClipboardJS(button,
        {
            target: target,
            text: function ()
            {
                return target.href;
            }
        });


        clipboard.on('success', function (e)
        {
            let checkIcon = button.querySelector('.bi-check');
            let copyIcon  = button.querySelector('.bi-copy');

            if (checkIcon)
            {
                return;
            }

            checkIcon = document.createElement('i');
            checkIcon.classList.add('bi');
            checkIcon.classList.add('bi-check');
            checkIcon.classList.add('fs-4');
            button.appendChild(checkIcon);

            const classes = ['text-success', 'fw-boldest'];

            target.classList.add(...classes);
            button.classList.add('success');
            copyIcon.classList.add('d-none');

            setTimeout(function ()
            {
                copyIcon.classList.remove('d-none');
                button.removeChild(checkIcon);
                target.classList.remove(...classes);
                button.classList.remove('success');
            }, 3000)
        });

        let clickTimer = null;

        document.querySelectorAll('.item.folder').forEach(item => {

            item.addEventListener('click', function () {

                clickTimer = setTimeout(() => { selectFolder(this); }, 250);

            });

            item.addEventListener('dblclick', function () {

                clearTimeout(clickTimer);
                enterFolder(this.dataset.url);

            });

        });

        function selectFolder(el)
        {
            document.querySelectorAll('.item').forEach(i => i.classList.remove('active'));
            el.classList.add('active');

            showFolderInfo(el);
        }

        function enterFolder(path)
        {
            window.location.href = path;
        }

        function showFolderInfo(el)
        {
            const panel = document.getElementById('preview-panel');

            panel.querySelector('.empty').classList.add('d-none');
            panel.querySelector('.content').classList.remove('d-none');

            const name = el.dataset.name;
            const url  = el.dataset.url;
            const img  = document.getElementById('preview-image');
            const icon = document.getElementById('preview-icon-selector');


            document.getElementById('p-delete').innerHTML     = '<i class="bi bi-trash fs-4"></i> Klasörü Sil';
            document.getElementById('p-name').innerText       = name;
            document.getElementById('p-delete').dataset.title = name + ' isimli klasörü';
            document.getElementById('p-size').innerText       = '-';
            document.getElementById('p-modified').innerText    = '-';
            document.getElementById('p-delete').dataset.route = document.getElementById('p-delete').dataset.route + '/' + name;

            $('#p-url').closest('li').addClass('d-none');

            icon.getElementsByTagName('i')[0].className = 'bi bi-folder-fill';
            icon.classList.remove('d-none');
            img.classList.add('d-none');
        }
    </script>
@endsection