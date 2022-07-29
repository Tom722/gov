define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ask/question/index',
                    add_url: 'ask/question/add',
                    edit_url: 'ask/question/edit',
                    del_url: 'ask/question/del',
                    multi_url: 'ask/question/multi',
                    table: 'ask_question',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user_id', title: __('User_id'), formatter: Table.api.formatter.search},
                        {field: 'user.nickname', title: __('Nickname'), operate: false},
                        {field: 'category_id', title: __('Category_id'), formatter: Table.api.formatter.search},
                        {field: 'category.name', title: __('Category_name'), operate: false},
                        {field: 'title', title: __('Title'), width: "200"},
                        {field: 'price', title: __('Price'), operate: 'BETWEEN'},
                        {field: 'score', title: __('Score'), operate: 'BETWEEN'},
                        {field: 'flag', title: __('Flag'), searchList: {"index": __('Index'), "hot": __('Hot'), "recommend": __('Recommend')}, operate: 'FIND_IN_SET', formatter: Table.api.formatter.label, visible: false},
                        {field: 'voteup', title: __('Voteup'), visible: false},
                        {field: 'votedown', title: __('Votedown'), visible: false},
                        {field: 'followers', title: __('Followers'), visible: false},
                        {field: 'views', title: __('Views'), visible: false},
                        {field: 'collections', title: __('Collections'), visible: false},
                        {field: 'thanks', title: __('Thanks'), visible: false},
                        {field: 'reports', title: __('Reports'), visible: false},
                        {field: 'answers', title: __('Answers')},
                        {field: 'comments', title: __('Comments'), visible: false},
                        {field: 'peeps', title: __('Peeps'), visible: false},
                        {field: 'best_answer_id', title: __('Best_answer_id'), visible: false},
                        {field: 'isanonymous', title: __('Isanonymous'), visible: false, visible: false},
                        {
                            field: 'url', title: __('Url'), formatter: function (value, row, index) {
                                return '<a href="' + row['url'] + '" target="_blank" class="btn btn-xs btn-warning">查看</a>'
                            }, operate: false
                        },
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"normal": __('Normal'), "hidden": __('Hidden'), "solved": __('Solved'), "closed": __('Closed')}, formatter: Table.api.formatter.status},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        recyclebin: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    'dragsort_url': ''
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: 'ask/question/recyclebin',
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user_id', title: __('User_id'), formatter: Table.api.formatter.search},
                        {field: 'title', title: __('Title'), align: 'left'},
                        {
                            field: 'url', title: __('Url'), formatter: function (value, row, index) {
                                return '<a href="' + row['url'] + '" target="_blank" class="btn btn-xs btn-warning">查看</a>'
                            }, operate: false
                        },
                        {
                            field: 'deletetime',
                            title: __('Deletetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            width: '130px',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'Restore',
                                    text: __('Restore'),
                                    classname: 'btn btn-xs btn-info btn-ajax btn-restoreit',
                                    icon: 'fa fa-rotate-left',
                                    url: 'ask/question/restore'
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'ask/question/destroy'
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
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
