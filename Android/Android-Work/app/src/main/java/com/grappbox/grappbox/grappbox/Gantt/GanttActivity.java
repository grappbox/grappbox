package com.grappbox.grappbox.grappbox.Gantt;

import android.app.Activity;
import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;

import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;

public class GanttActivity extends AppCompatActivity {

    private class TaskListener implements GanttChart.GanttTaskListener {
        @Override
        public void onTaskClick(Task task) {
            Intent intent = new Intent();
            Log.e("GANTT", "Selected : " + task.getId());
            intent.putExtra(Task.INTENT_TASK_ID, task.getId());
            setResult(Activity.RESULT_OK, intent);
            finish();
        }
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_gantt);
        setTitle("GANTT");
        GanttChart ganttView = (GanttChart) findViewById(R.id.ganttchart);
        APIGetAllTask gettaskTask = new APIGetAllTask(this, new APIGetAllTask.APIGetAllTaskListener() {
            @Override
            public void onTaskFetched(ArrayList<Task> tasks) {
                ganttView.SetTasks(tasks);
                ganttView.setTaskListener(new TaskListener());
            }
        });
        gettaskTask.execute(String.valueOf(SessionAdapter.getInstance().getCurrentSelectedProject()));
    }
}
