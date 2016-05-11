package com.grappbox.grappbox.grappbox.Gantt;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;

import com.grappbox.grappbox.grappbox.R;

public class TaskDetailActivity extends AppCompatActivity {
    public final static String NEW_MODE = "com.grappbox.grappbox.grappbox.Gantt.TaskDetailActivity.new_mode";
    public final static String EDIT_MODE = "com.grappbox.grappbox.grappbox.Gantt.TaskDetailActivity.edit_mode";

    private void onCreateEditMode(Intent infos)
    {
        String taskID = infos.getStringExtra(Task.INTENT_TASK_ID);
        Button save = (Button) findViewById(R.id.btn_save);
        if (taskID == null || taskID.isEmpty())
        {
            Log.e("Grappbox TaskDetail", "Try to edit a task, but there is no Task ID in passed intent, please see Task.INTENT_TASK_ID");
            onCreateNewMode(infos);
            return;
        }
        GetTaskInformationsTask task = new GetTaskInformationsTask(getBaseContext(), new GetTaskInformationsTask.APIGetTaskInformationListener() {
            @Override
            public void onTaskFetched(Task task) {
                //TODO : bind task model to view layout
            }
        });
        task.execute(taskID);
        save.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                //TODO : edit task
            }
        });
    }

    private void onCreateNewMode(Intent infos)
    {
        Button save = (Button) findViewById(R.id.btn_save);

        save.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                //TODO: save new task
            }
        });
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_task_detail);
        Intent infos = getIntent();

        if (infos.getCategories().contains(EDIT_MODE))
            onCreateEditMode(infos);
        else
            onCreateNewMode(infos);
    }


}
