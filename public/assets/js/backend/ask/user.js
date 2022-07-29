define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ask/user/index',
                    add_url: 'ask/user/add',
                    edit_url: 'ask/user/edit',
                    del_url: 'ask/user/del',
                    multi_url: 'ask/user/multi',
                    table: 'ask_user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'user_id',
                sortName: 'user_id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'user_id', title: __('User Id'), formatter: Table.api.formatter.search},
                        {field: 'category.name', title: __('Category_id'), operate: false},
                        {field: 'basic.nickname', title: __('Nickname'), operate: 'like'},
                        {field: 'flag', title: __('Flag'), searchList: {"index": __('Index'), "recommend": __('Recommend'), "new": __('New')}, operate: 'FIND_IN_SET', formatter: Table.api.formatter.label},
                        {field: 'followers', title: __('Followers')},
                        {field: 'questions', title: __('Questions')},
                        {field: 'answers', title: __('Answers')},
                        {field: 'comments', title: __('Comments')},
                        {field: 'articles', title: __('Articles')},
                        {field: 'collections', title: __('Collections')},
                        {field: 'adoptions', title: __('Adoptions')},
                        {field: 'views', title: __('Views')},
                        {field: 'isexpert', title: __('Isexpert')},
                        {field: 'experttitle', title: __('Experttitle')},
                        {field: 'invites', title: __('Invites')},
                        {field: 'messages', title: __('Messages')},
                        {field: 'notifications', title: __('Notifications')},
                        {field: 'unadopted', title: __('Unadopted')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });


            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
