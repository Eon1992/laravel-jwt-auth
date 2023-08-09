<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;


class TaskController extends Controller
{

    protected $user;
    protected $response = [];
    protected $error_msg = null;

    public function __construct()
    {
        //

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'orderBy' => 'nullable|string|in:taskName,status,id,dueDate',
            'sortBy' => 'nullable|string|in:asc,desc'
        ],[
            'sortBy.in' => 'Sort order should be asc or desc',
            'orderBy.in' => 'Order by should be in taskName,status,id,dueDate'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {

            $this->error_msg = $validator->errors()->toArray();
            $this->error_msg = array_values($this->error_msg)[0][0];

            $this->response = [
                "status" => "error",
                "error" => true,
                "response_code" => 400,
                "message" => $this->error_msg,
            ];

        } else {

            $status = isset($request->status) ? $request->status : "";
            $dueDate = empty($request->dueDate) ? "" : $request->dueDate;
            $paginationLimit = empty($request->paginationLimit) ? "" : (int)$request->paginationLimit;
            $orderBy = empty($request->orderBy) ? "id" : $request->orderBy;
            $sortBy = empty($request->sortBy) ? "asc" : $request->sortBy;

            $allTasks = Task::when($status, function ($query) use ($status) {
                return $query->where('status', $status);
            })->when($dueDate, function ($query) use ($dueDate) {
                return $query->where('dueDate', $dueDate);
            })->orderby($orderBy,$sortBy)->get(array('id','user_id','taskName','description','status','dueDate'))->toArray();

            if(!empty($paginationLimit)) {

                $allTasks = array_chunk($allTasks,$paginationLimit);

            }

            $this->response = [
                "status" => "success",
                "error" => false,
                "response_code" => 200,
                "data" => $allTasks,
                "message" => "All tasks List",
            ];
        }

        return response($this->response, $this->response['response_code']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $this->user = JWTAuth::parseToken()->authenticate();
        //Validate data
        $data = $request->only('taskName', 'description', 'dueDate');

        $validator = Validator::make($data, [
            'taskName' => 'required|string|unique:tasks',
            'description' => 'required',
            'dueDate' => 'required|date'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {

            $this->error_msg = $validator->errors()->toArray();
            $this->error_msg = array_values($this->error_msg)[0][0];

            $this->response = [
                "status" => "error",
                "error" => true,
                "response_code" => 400,
                "message" => $this->error_msg,
            ];

        } else {

            //Request is valid, create new task
            $task = [];

            $task['user_id'] = $this->user->id;
            $task['taskName'] = $data['taskName'];
            $task['description'] = $data['description'];
            $task['dueDate'] = $data['dueDate'];

            $createdTask= Task::create($task);

            $createdTask->status = '0';
            $createdTask->taskStatus = 'Pending';

            //Task created, return success response

            $this->response = [
                "status" => "success",
                "error" => false,
                "response_code" => 200,
                "data" => $createdTask,
                "message" => "Successfully task created",
            ];
        }

        return response($this->response, $this->response['response_code']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $task = Task::where('id',$id)->get(array('id','taskName','status','description','dueDate'))->first();

        if (empty($task['id'])) {

            $this->response = [
                "status" => "error",
                "error" => true,
                "response_code" => 400,
                "message" => "Sorry! No task found",
            ];

        } else {

            if($task['status'] == '0') {

                $task['taskStatus'] = 'Pending';

            } elseif($task['status'] == '1') {

                $task['taskStatus'] = 'Inprogress';

            } elseif($task['status'] == '2') {

                $task['taskStatus'] = 'Completed';

            } else {

                $task['taskStatus'] = 'Deleted';
            }

            $this->response = [
                "status" => "success",
                "error" => false,
                "response_code" => 200,
                "data" => $task,
                "message" => "Successfully task fetched",
            ];
        }

        return response($this->response, $this->response['response_code']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'taskId' => 'required|integer|min:1',
            'taskName' => 'string|unique:tasks,taskName,'.$request->taskId,
            'description' => 'required',
            'dueDate' => 'required|date',
            'status' => 'required|in:0,1,2,3'
        ]);
        if ($validator->fails()) {

            $this->error_msg = $validator->errors()->toArray();
            $this->error_msg = array_values($this->error_msg)[0][0];

            $this->response = [
                "status" => "error",
                "error" => true,
                "response_code" => 400,
                "message" => $this->error_msg,
            ];
        } else {

            $taskUpdate = [];

            $taskUpdate['taskName'] = $request->taskName;
            $taskUpdate['description'] = $request->description;
            $taskUpdate['dueDate'] = $request->dueDate;
            $taskUpdate['status'] = $request->status;

            $updateTask = Task::where('id',$request->taskId)->update($taskUpdate);

            if($updateTask) {

                $this->response = [
                    "status" => "success",
                    "error" => false,
                    "response_code" => 200,
                    "message" => "Task updated Successfully"
                ];

            } else {

                $this->response = [
                    "status" => "error",
                    "error" => true,
                    "response_code" => 400,
                    "message" => "Sorry! there was error while updating the task"
                ];

            }

        }

        return response($this->response, $this->response['response_code']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'taskId' => 'required|integer|min:1',
            'isPermanentDelete' => 'required|integer|in:0,1'
        ]);
        if ($validator->fails()) {

            $this->error_msg = $validator->errors()->toArray();
            $this->error_msg = array_values($this->error_msg)[0][0];

            $this->response = [
                "status" => "error",
                "error" => true,
                "response_code" => 400,
                "message" => $this->error_msg,
            ];
        } else {

            $getTask = Task::where('id',$request->taskId)->get(array('id'))->first();

            if(empty($getTask['id'])) {

                $this->response = [
                    "status" => "error",
                    "error" => true,
                    "response_code" => 400,
                    "message" => "Sorry! Task not found"
                ];

            } else {

                $isPermanentDelete = empty($request->isPermanentDelete) ? '0' : '1';

                if($isPermanentDelete == '1') {

                    Task::where('id',$request->taskId)->delete();

                    $this->response = [
                        "status" => "success",
                        "error" => false,
                        "response_code" => 200,
                        "message" => "Task is permanently deleted"
                    ];

                } else {

                    $taskUpdate = [];

                    // Temporary delete
                    $taskUpdate['status'] = '3';

                    $updateTask = Task::where('id',$request->taskId)->update($taskUpdate);

                    if($updateTask) {

                        $this->response = [
                            "status" => "success",
                            "error" => false,
                            "response_code" => 200,
                            "message" => "Task is temporarily deleted"
                        ];

                    } else {

                        $this->response = [
                            "status" => "error",
                            "error" => true,
                            "response_code" => 400,
                            "message" => "Sorry! there was error while deleted the task"
                        ];

                    }

                }
            }

        }

        return response($this->response, $this->response['response_code']);
    }
}
