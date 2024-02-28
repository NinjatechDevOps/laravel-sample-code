@foreach($bomFiles as $bomFile)
    <div class="bg-white-radius open-dom">
        <a href="javascript:void(0);" title="Close" class="close-dom remove-bom" data-filename="{{$bomFile['name']}}"><span class="icon-close"></span> </a>
        <div class="bom-col d-flex flex-direction-column">
            <div class="subtitle">{{$bomFile['type']}}</div>
            <div class="body2-text">
                <div class="file-name" style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;margin: 0 20px;"> {{$bomFile['original_name']}}</div>
                <span class="caption">{{$bomFile['size']}}</span>
            </div>
        </div>
    </div>
@endforeach
