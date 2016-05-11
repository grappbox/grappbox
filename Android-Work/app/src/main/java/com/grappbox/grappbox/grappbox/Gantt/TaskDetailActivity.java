package com.grappbox.grappbox.grappbox.Gantt;

import android.content.Intent;
import android.os.Bundle;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.util.Pair;
import android.view.View;
import android.widget.AdapterView;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.LinearLayout;
import android.widget.Spinner;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;

public class TaskDetailActivity extends AppCompatActivity {
    public final static String NEW_MODE = "com.grappbox.grappbox.grappbox.Gantt.TaskDetailActivity.new_mode";
    public final static String EDIT_MODE = "com.grappbox.grappbox.grappbox.Gantt.TaskDetailActivity.edit_mode";

    private boolean isTaskFetched = false;
    private TextView title, description, date, done;
    private CheckBox isMilestone;
    private Button addDependency, editSave;
    private String mode;
    private ArrayList<DependencyContainer> dependencies;

    private class DependencyContainer
    {
        public View dependencyView;
        public String title;
        public Task.ELinkType linkType;
        public String ID;
        public boolean isDeleted;

        public void setLinkType(Task.ELinkType link)
        {
            linkType = link;
        }

        public DependencyContainer(String title, Task.ELinkType linkType, String ID) {
            isDeleted = false;
            this.title = title;
            this.linkType = linkType;
            this.ID = ID;
            dependencyView = getLayoutInflater().inflate(R.layout.dependency_item_view, null);
            ((TextView) dependencyView.findViewById(R.id.txt_title)).setText(title);
            Spinner linkChoices = (Spinner) findViewById(R.id.spin_types);
            linkChoices.setOnItemClickListener(new AdapterView.OnItemClickListener() {
                @Override
                public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                }
            });
            //TODO : Initiate the spinner with choices
            ((Button)dependencyView.findViewById(R.id.btn_delete)).setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    isDeleted = !isDeleted;
                    ((Button)dependencyView.findViewById(R.id.btn_delete)).setText(isDeleted ? "Recover" : "Delete");
                    int green = ContextCompat.getColor(getApplicationContext(), R.color.colorGrappboxGreen);
                    int red = ContextCompat.getColor(getApplicationContext(), R.color.colorGrappboxRed);
                    ((Button)dependencyView.findViewById(R.id.btn_delete)).setBackgroundColor(isDeleted ? green : red);
                    setReadOnly(isDeleted);
                }
            });
        }

        public void setReadOnly(boolean ro)
        {
            ((TextView) dependencyView.findViewById(R.id.txt_title)).setEnabled(!ro);
            ((Spinner) dependencyView.findViewById(R.id.spin_types)).setEnabled(!ro);
        }

    }

    private DependencyContainer addDependencyDisplay(String title, String id, Task.ELinkType link)
    {
        DependencyContainer dependencyContainer = new DependencyContainer(title, link, id);
        dependencies.add(dependencyContainer);
        return dependencyContainer;
    }

    private void setReadOnly(boolean ro)
    {
        title.setEnabled(!ro);
        description.setEnabled(!ro);
        date.setEnabled(!ro);
        done.setEnabled(!ro);
        isMilestone.setEnabled(!ro);
        addDependency.setEnabled(!ro);
        if (ro)
            editSave.setText("Edit");
        else
            editSave.setText("Save");
    }

    private void initView()
    {
        editSave = (Button) findViewById(R.id.btn_save);
        title = (TextView) findViewById(R.id.etxt_title);
        description = (TextView) findViewById(R.id.etxt_description);
        date = (TextView) findViewById(R.id.etxt_due_date);
        done = (TextView) findViewById(R.id.etxt_done);
        isMilestone = (CheckBox) findViewById(R.id.is_milestone);
    }

    private void onCreateEditMode(Intent infos)
    {
        String taskID = infos.getStringExtra(Task.INTENT_TASK_ID);

        if (taskID == null || taskID.isEmpty())
        {
            Log.e("Grappbox TaskDetail", "Try to edit a task, but there is no Task ID in passed intent, please see Task.INTENT_TASK_ID");
            onCreateNewMode(infos);
            return;
        }
        initView();
        setReadOnly(true);
        GetTaskInformationsTask task = new GetTaskInformationsTask(this, new GetTaskInformationsTask.APIGetTaskInformationListener() {
            @Override
            public void onTaskFetched(Task task) {
                if (task == null)
                    return;
                isTaskFetched = true;
                title.setText(task.getTitle());
                description.setText(task.getDescription());
                date.setText(task.getEndDate().toString());
                done.setText(task.getAccomplishedPercent());
                isMilestone.setChecked(task.IsMilestone());
                for (Pair<String, Task.ELinkType> current : task.getLinks())
                {
                    GetTaskInformationsTask getDependecyTask = new GetTaskInformationsTask(getBaseContext(), new GetTaskInformationsTask.APIGetTaskInformationListener() {
                        @Override
                        public void onTaskFetched(Task task) {
                            ((LinearLayout)findViewById(R.id.ll_dependencies)).addView(addDependencyDisplay(task.getTitle(), task.getId(), current.second).dependencyView);
                        }
                    });
                    getDependecyTask.execute(current.first);
                }
            }
        });
        task.execute(taskID);
        editSave.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                //TODO : edit task (linkAPI)
            }
        });
    }

    private void onCreateNewMode(Intent infos)
    {
        initView();
        setReadOnly(false);
        editSave.setOnClickListener(new View.OnClickListener() {
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
