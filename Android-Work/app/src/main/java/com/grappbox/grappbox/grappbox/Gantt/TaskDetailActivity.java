package com.grappbox.grappbox.grappbox.Gantt;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.TextView;

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
                TextView title, description, date;
                CheckBox isMilestone;

                title = (TextView) findViewById(R.id.etxt_title);
                description = (TextView) findViewById(R.id.etxt_description);
                date = (TextView) findViewById(R.id.etxt_due_date);
                isMilestone = (CheckBox) findViewById(R.id.is_milestone);
                title.setText(task.getTitle());
                description.setText(task.getDescription());
                date.setText(task.getEndDate().toString());
                isMilestone.setChecked(task.IsMilestone());
                //TODO : bind task model to view layout
            }
        });
        task.execute(taskID);
        save.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                //TODO : edit task (linkAPI)
            }
        });
    }

    private void onCreateNewMode(Intent infos)
    {
        Button save = (Button) findViewById(R.id.btn_save);

        save.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                //TODO: save new task (linkAPI)
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
