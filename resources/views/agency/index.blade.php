@extends('phobrv::adminlte3.layout')

@section('header')
<ul>
	<li>
		<a href="/admin/term/region"  class="btn btn-default float-left">
			<i class="fa fa-backward"></i> @lang('Back')
		</a>
	</li>
	<li>
		{{ Form::open(array('route'=>'agency.updateUserSelect','method'=>'post')) }}
		<table class="form" width="100%" border="0" cellspacing="1" cellpadding="1">
			<tbody>
				<tr>
					<td style="text-align:center; padding-right: 10px;">
						<div class="form-group">
							{{ Form::select('select', $data['provinces'],(isset($data['select']) ? $data['select'] : '0'),array('id'=>'choose','class'=>'form-control')) }}
						</div>
					</td>
					<td>
						<div class="form-group">
							<button id="btnSubmitFilter" type="submit" class="btn btn-primary ">@lang('Filter')</button>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
		{{Form::close()}}
	</li>
</ul>
@endsection

@section('content')
<div class="row">
	<div class="col-md-5">
		<div class="card">
			<div class="box-header">
				Create/Edit Agency
			</div>
			<form class="form-horizontal" id="formSubmit" method="post" action="{{isset($data['post']) ? route('agency.update',array('agency'=>$data['post']->id)) : route('agency.store')}}">
				<div class="card-body">
					<input type="hidden" name="type" value="{{ config('option.post_type.agency') }}">
					@isset($data['post']) @method('put') @endisset
					@csrf
					@include('phobrv::input.inputSelect',['label'=>'Province','key'=>'term_id','array'=>$data['provinces'],'value'=>$data['select'],'required'=>true])
					@include('phobrv::input.inputText',['label'=>'Name','key'=>'title','required'=>true])
					@include('phobrv::input.inputText',['label'=>'Địa chỉ','key'=>'excerpt','required'=>true])
					@include('phobrv::input.inputText',['label'=>'Phone','key'=>'thumb','inputType'=>'number'])
					@include('phobrv::input.inputTextarea',['label'=>'Map','key'=>'content','style'=>'short','row'=>'5'])
				</div>
				<div class="card-footer">
					<button class="pull-right btn btn-primary" type="submit">{{$data['submit_label'] ?? ''}}</button>
				</div>
			</form>
		</div>
	</div>
	<div class="col-md-7">
		<div class="card">
			<div class="card-body">
				<table id="example1" class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>#</th>
							<th>Name</th>
							<th>Address</th>
							<th>Phone</th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						@isset($data['posts'])
						@foreach($data['posts'] as $r)
						<tr>
							<td align="center">{{$loop->index+1}}</td>
							<td>{{$r->title}}</td>
							<td>{{$r->excerpt}}</td>
							<td>{{$r->thumb}}</td>
							<td align="center" width="50px">
								<a href="{{route('agency.changeOrder',['agency_id'=>$r->id,'type'=>'plus'])}}"> <i class="fa fa-fw fa-chevron-circle-up"></i>
								</a>

								<a href="{{route('agency.changeOrder',['agency_id'=>$r->id,'type'=>'minus'])}}">
									<i class="fa fa-fw fa-chevron-circle-down"></i>
								</a>
							</td>
							<td align="center"  width="50px">
								<a href="{{route('agency.edit',['agency'=>$r->id])}}"><i class="fa fa-edit" title="Sửa"></i></a>
								&nbsp;&nbsp;&nbsp;
								<a style="color: red" href="#" onclick="destroy('destroy{{$r->id}}')"><i class="fa fa-times" title="Sửa"></i></a>
								<form id="destroy{{$r->id}}" action="{{ route('agency.destroy',array('agency'=>$r->id)) }}" method="post" style="display: none;">
									@method('delete')
									@csrf
								</form>
							</td>
						</tr>

						@endforeach
						@endif
					</tbody>

				</table>

			</div>
		</div>
	</div>
</div>
@endsection

@section('styles')

@endsection

@section('scripts')
<script type="text/javascript">
	function destroy(form){
		var anwser =  confirm("Bạn muốn xóa đại lý này?");
		if(anwser){
			event.preventDefault();
			document.getElementById(form).submit();
		}
	}
</script>
@endsection
