@extends('layouts.admin')

@section('title',tr('email_templates'))

@section('content-header',tr('email_templates'))

@section('breadcrumb')

	<li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i>{{ tr('home') }}</a></li>
	<li class="active">{{ tr('email_templates') }}</li>

@endsection

@section('content')

	<div class="row">

		<div class="col-xs-12">

			<div class="box box-primary">

				<div class="box-header label-primary">
					<b style="font-size: 18px;">{{ tr('email_templates') }}</b>
				</div>

				<div class="box-body">

					<table id = "example1" class="table table-bordered table-striped">

						<thead>
							<tr>
								<th>{{ tr('id') }}</th>
								<th>{{ tr('template') }}</th>
								<th>{{ tr('subject') }}</th>
								<th>{{ tr('action') }}</th>
							</tr>
						</thead>

						<tbody>

							@foreach($templates as $i => $template_details)

							<tr>
								<td>{{ $i+1 }}</td>

								<td>{{ getTemplateName($template_details->template_type) }}</td>

								<td>{{ $template_details->subject }}</td>
								
								<td>

									<ul class="admin-action btn btn-default">

										<li class="dropdown">
											<a class="dropdown-toggle" data-toggle="dropdown" href="#">{{ tr('action') }} <span class="caret"></span></a>
										
											<ul class="dropdown-menu">
												<li role="presentation">
													<a class = "menuitem"  tabindex= "-1" href="{{ route('admin.templates.edit',['template_id' => $template_details->id]) }}">{{ tr('edit') }}</a>
												</li>

												<li role="presentation">
													<a class="menuitem" tabindex="-1" href="{{ route('admin.templates.view', ['template_id' => $template_details->id]) }}">{{ tr('view') }}</a>
												</li>
											</ul>
										</li>
										
									</ul>

								</td>

							</tr>

							@endforeach

						</tbody>

					</table>
					
				</div>

			</div>

		</div>

	</div>

@endsection

