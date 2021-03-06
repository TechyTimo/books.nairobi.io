<?php

class CoursesController extends BaseController {

    /**
     * Course Repository
     *
     * @var Course
     */

    // protected $layout = 'layouts.booklayout';
    protected $course;

    public function __construct(Course $course)
    {
        
        $this->course = $course;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        // $courses = $this->course->all();
        $courses = Course::all();

        return View::make('courses.index', compact('courses'))->with("courses",$courses);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return View::make('courses.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $input = Input::all();

        $validation = Validator::make($input, Course::$rules);

        if ($validation->passes())
        {
            $this->course->create($input);
            return Redirect::route('courses.index');
        }

        return Redirect::route('courses.create')
            ->withInput()
            ->withErrors($validation)
            ->with('message', 'There were validation errors.');
    }
    public function ajaxByLetters() {
        $term = Input::get('coursename');
        $data = array();
        $query = DB::table('courses')->where('name', 'like', '%'.$term.'%')->take(5)->get();
        return json_encode($query);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        // $courses = $arrayName = array('courses' => 'courses.show', compact('course'));
        $course = $this->course->findOrFail($id);
        // var_dump(DB::table('courses')->where('id', $id)->get());
        if(DB::table('courses')->where('id', $id)->get()):
            return View::make('courses.show', compact('course'));
        else:
             return Redirect::route('courses.index')
                ->with('error', 'That course does not exist');
        endif;

    }

    public function back($id)
    {
        // $courses = $arrayName = array('courses' => 'courses.show', compact('course'));
        // $course = $this->course->findOrFail($id);

        // return View::make('courses.show', compact('course'));
        return Redirect::to('user/'.Auth::user()->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $course = $this->course->find($id);

        if (is_null($course))
        {
            return Redirect::route('courses.index');
        }

        return View::make('courses.edit', compact('course'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $input = array_except(Input::all(), '_method');
        $validation = Validator::make($input, Course::$rules);

        if ($validation->passes())
        {
            $course = $this->course->find($id);
            $course->update($input);

            return Redirect::route('courses.show', $id);
        }

        return Redirect::route('courses.edit', $id)
            ->withInput()
            ->withErrors($validation)
            ->with('message', 'There were validation errors.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->course->find($id)->delete();

         return Redirect::to('courses')->with('success', 'The course has been Deleted');
    }
    public function search()
    {
         return Redirect::to('courses/index')->with('info', 'Search Coming soon, use autocomplete');
    }

}