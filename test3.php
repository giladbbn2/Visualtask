<?php

$task = '{"taskId":"task1","tplId":"tpl1","queries":{"q1":{"type":"res1","select":[{"function":"date","fieldName":"insert_datetime","alias":"t"},{"function":"count","fieldName":"id"}],"from":"test.users","where":[{"fieldName":"id","fieldType":"num","op":">","val":0}],"groupBy":[{"function":"date","fieldName":"insert_datetime"}],"orderBy":[{"function":"date","fieldName":"insert_datetime","dir":"desc"}],"limit":{"offset":0,"size":10}}},"graphs":{"g1":{"HTMLElementId":"graph1","lib":"visualtaskgrid","queryId":"q1","fields":[{"fieldName":"t","fieldType":"datetime"},{"fieldName":"id","fieldType":"num","isSortable":false}],"header":{"values":["Insert Datetime","User ID"]},"isShowFiltering":true,"isShowAdvancedBox":true,"isShowFieldSelector":true,"isShowPager":true},"g2":{"HTMLElementId":"graph2","lib":"plotly","queryId":"q1","fields":[{"fieldName":"t"},{"fieldName":"id"}],"config":{"data":[{"type":"scatter","xFieldId":0,"yFieldId":1,"line":{"color":"red"}}],"layout":{"title":"number of inserted users each day","xaxis":{"type":"date"}}}}}}';

$config = array(
	"task" => $task,
	"entities" => 
);



