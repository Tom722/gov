define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'ask/answer/index',
                    add_url: 'ask/answer/add',
                    edit_url: 'ask/answer/edit',
                    del_url: 'ask/answer/del',
                    multi_url: 'ask/answer/multi',
                    table: 'ask_answer',
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
                        {field: 'question_id', title: __('Question_id'), formatter: Table.api.formatter.search},
                        {field: 'question.title', title: __('Title'), operate: false},
                        {field: 'reply_user_id', title: __('Reply_user_id'), visible: false},
                        {field: 'price', title: __('Price'), operate: 'BETWEEN', sortable: true},
                        {field: 'score', title: __('Score'), operate: 'BETWEEN', sortable: true},
                        {field: 'shares', title: __('Shares'), sortable: true, visible: false},
                        {field: 'voteup', title: __('Voteup'), sortable: true},
                        {field: 'votedown', title: __('Votedown'), sortable: true},
                        {field: 'sales', title: __('Sales'), sortable: true},
                        {field: 'comments', title: __('Comments'), sortable: true},
                        {field: 'collections', title: __('Collections'), sortable: true, visible: false},
                        {field: 'thanks', title: __('Thanks'), sortable: true, visible: false},
                        {field: 'reports', title: __('Reports'), sortable: true, visible: false},
                        {
                            field: 'question_url', title: __('Url'), formatter: function (value, row, index) {
                                return '<a href="' + row['question_url'] + '" target="_blank" class="btn btn-xs btn-warning">查看</a>'
                            }, operate: false
                        },
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime, visible: false},
                        {field: 'adopttime', title: __('Adopttime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"normal": __('Normal'), "hidden": __('Hidden'), "closed": __('Closed')}, formatter: Table.api.formatter.status},
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
                url: 'ask/answer/recyclebin',
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'user_id', title: __('User_id'), formatter: Table.api.formatter.search},
                        {field: 'content', title: __('Content'), align: 'left'},
                        {
                            field: 'question_url', title: __('Url'), formatter: function (value, row, index) {
                                return '<a href="' + row['question_url'] + '" target="_blank" class="btn btn-xs btn-warning">查看</a>'
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
                                    url: 'ask/answer/restore'
                                },
                                {
                                    name: 'Destroy',
                                    text: __('Destroy'),
                                    classname: 'btn btn-xs btn-danger btn-ajax btn-destroyit',
                                    icon: 'fa fa-times',
                                    url: 'ask/answer/destroy'
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
