@extends('layouts.bookcheetah')

@section('content')

<h1>Show Courselist</h1>

<p>{{ link_to_route('courselists.index', 'Return to all courselists') }}</p>

<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Userid</th>
				<th>Courseid</th>
				<th>Username</th>
				<th>Coursename</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td>{{{ $courselist->userid }}}</td>
					<td>{{{ $courselist->courseid }}}</td>
					<td>{{{ $courselist->username }}}</td>
					<td>{{{ $courselist->coursename }}}</td>
                    <td>{{ link_to_route('courselists.edit', 'Edit', array($courselist->id), array('class' => 'btn btn-info')) }}</td>
                    <td>
                        {{ Form::open(array('method' => 'DELETE', 'route' => array('courselists.destroy', $courselist->id))) }}
                            {{ Form::submit('Delete', array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                    </td>
        </tr>
    </tbody>
</table>

@stop