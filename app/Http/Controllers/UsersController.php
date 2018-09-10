<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    /**
     * 用户注册页面
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * 用户注册动作
     * 将用户提交的信息存储到数据库，并重定向到其个人页面；
     * 在网页顶部位置显示注册成功的提示信息；
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /*
         * required 来验证用户名是否为空
         * min 和 max 来限制用户名所填写的最小长度和最大长度
         * email 格式验证
         * unique:users 针对于数据表 users 做唯一性验证
         * confirmed 来进行密码匹配验证
         *
         * */
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            //用户模型 User::create() 创建成功后会返回一个用户对象，并包含新注册用户的所有信息
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        Auth::login($user);//注册后自动登录
        //使用 session() 方法来访问会话实例. flash 只在下一次的请求内有效
        //之后我们可以使用 session()->get('success') 通过键名来取出对应会话中的数据
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');

        return redirect()->route('users.show', [$user]);
        //route() 方法会自动获取 Model 的主键, 以上代码等同于：
        //redirect()->route('users.show', [$user->id]);
    }

    public function index()
    {
        //$users = User::all();
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * 用户个人信息显示页面
     *
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    //修改个人资料页面
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit',compact('user'));
    }

    //修改个人资料动作
    public function update(User $user, Request $request)
    {
        $this->validate($request, [
           'name' => 'required|max:50',
           'password' => 'nullable|confirmed|min:6'
        ]);

        $this->authorize('update', $user);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user->id);
    }

    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }
}
