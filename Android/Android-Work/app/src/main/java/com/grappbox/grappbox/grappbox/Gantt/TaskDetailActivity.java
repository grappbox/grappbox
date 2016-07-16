package com.grappbox.grappbox.grappbox.Gantt;

import android.app.Activity;
import android.app.DatePickerDialog;
import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.drawable.Drawable;
import android.os.Bundle;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.util.Pair;
import android.view.View;
import android.widget.AdapterView;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.DatePicker;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.Spinner;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.Calendar;
import java.util.Date;
import java.util.GregorianCalendar;
import java.util.Objects;

public class TaskDetailActivity extends AppCompatActivity {
    public final static String NEW_MODE = "com.grappbox.grappbox.grappbox.Gantt.TaskDetailActivity.new_mode";
    public final static String EDIT_MODE = "com.grappbox.grappbox.grappbox.Gantt.TaskDetailActivity.edit_mode";

    private boolean isTaskFetched = false;
    private Task editTaskContent;
    private TextView title, description, done;
    private Button btn_startdate, btn_duedate;
    private CheckBox isMilestone;
    private ImageButton addDependency, addResources;
    private Button editSave;
    private String mode;
    private ArrayList<DependencyContainer> dependencies;
    private ArrayList<ResourcesContainer> resources;
    private boolean curr_ro;
    private java.text.DateFormat dateFormatter;

    public class DependencyContainer
    {
        public View dependencyView;
        public String title;
        public Task.ELinkType linkType;
        public String ID;
        public boolean isDeleted;


        public void setEnabled(boolean enabled)
        {
            dependencyView.findViewById(R.id.spin_types).setEnabled(enabled);
            dependencyView.findViewById(R.id.txt_title).setEnabled(enabled);
            dependencyView.findViewById(R.id.btn_delete).setEnabled(enabled);
            dependencyView.findViewById(R.id.btn_delete).setVisibility(enabled ? View.VISIBLE : View.GONE);
        }

        public void setReadOnly(boolean ro)
        {
            ((TextView) dependencyView.findViewById(R.id.txt_title)).setEnabled(!ro);
            ((Spinner) dependencyView.findViewById(R.id.spin_types)).setEnabled(!ro);
        }

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
            Spinner linkChoices = (Spinner) dependencyView.findViewById(R.id.spin_types);
            linkChoices.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
                @Override
                public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                    String selected = getResources().getStringArray(R.array.task_dependencies_universal)[position];
                    switch (selected)
                    {
                        case "start_start":
                            setLinkType(Task.ELinkType.START_TO_START);
                            break;
                        case "start_end":
                            setLinkType(Task.ELinkType.START_TO_END);
                            break;
                        case "end_end":
                            setLinkType(Task.ELinkType.END_TO_END);
                            break;
                        case "end_start":
                            setLinkType(Task.ELinkType.END_TO_START);
                            break;
                        default:
                            isDeleted = true;
                            break;
                    }
                }

                @Override
                public void onNothingSelected(AdapterView<?> parent) {
                    setLinkType(linkType);
                }
            });
            ((ImageButton)dependencyView.findViewById(R.id.btn_delete)).setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    isDeleted = !isDeleted;
                    Drawable cross = ContextCompat.getDrawable(getBaseContext(), android.R.drawable.ic_delete);
                    Drawable recover = ContextCompat.getDrawable(getBaseContext(), R.mipmap.ic_undo);
                    ((ImageButton)dependencyView.findViewById(R.id.btn_delete)).setImageDrawable(isDeleted ? recover : cross);
                    int green = ContextCompat.getColor(getApplicationContext(), R.color.colorGrappboxGreen);
                    int red = ContextCompat.getColor(getApplicationContext(), R.color.colorGrappboxRed);
                    ((ImageButton)dependencyView.findViewById(R.id.btn_delete)).setColorFilter(isDeleted ? green : red);
                    setReadOnly(isDeleted);
                }
            });
        }

    }

    public class ResourcesContainer
    {
        public View resourceView;
        public String title;
        public String ID;
        public boolean isDeleted;

        public void setEnabled(boolean enabled)
        {
            resourceView.findViewById(R.id.txt_title).setEnabled(enabled);
            resourceView.findViewById(R.id.btn_delete).setEnabled(enabled);
            resourceView.findViewById(R.id.btn_delete).setVisibility(enabled ? View.VISIBLE : View.GONE);
        }

        public ResourcesContainer(String title, String ID) {
            this.title = title;
            this.ID = ID;
            resourceView = getLayoutInflater().inflate(R.layout.resource_item_view, null);
            ((TextView) resourceView.findViewById(R.id.txt_title)).setText(title);
            ((ImageButton)resourceView.findViewById(R.id.btn_delete)).setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    isDeleted = !isDeleted;
                    Drawable cross = ContextCompat.getDrawable(getBaseContext(), android.R.drawable.ic_delete);
                    Drawable recover = ContextCompat.getDrawable(getBaseContext(), R.mipmap.ic_undo);
                    ((ImageButton)resourceView.findViewById(R.id.btn_delete)).setImageDrawable(isDeleted ? recover : cross);
                    int green = ContextCompat.getColor(getApplicationContext(), R.color.colorGrappboxGreen);
                    int red = ContextCompat.getColor(getApplicationContext(), R.color.colorGrappboxRed);
                    ((ImageButton)resourceView.findViewById(R.id.btn_delete)).setColorFilter(isDeleted ? green : red);
                    ((TextView) resourceView.findViewById(R.id.txt_title)).setEnabled(!isDeleted);
                }
            });
        }
    }

    private DependencyContainer addDependencyDisplay(String title, String id, Task.ELinkType link)
    {
        DependencyContainer dependencyContainer = new DependencyContainer(title, link, id);
        dependencies.add(dependencyContainer);
        dependencyContainer.setEnabled(!curr_ro);
        return dependencyContainer;
    }

    private ResourcesContainer addResourceDisplay(String name, String id)
    {
        ResourcesContainer resourceContainer = new ResourcesContainer(name, id);
        resources.add(resourceContainer);
        resourceContainer.setEnabled(!curr_ro);
        return resourceContainer;
    }

    private void setReadOnly(boolean ro)
    {
        curr_ro = ro;
        title.setEnabled(!ro);
        description.setEnabled(!ro);
        btn_duedate.setEnabled(!ro);
        btn_startdate.setEnabled(!ro);
        done.setEnabled(!ro);
        isMilestone.setEnabled(!ro);
        addDependency.setEnabled(!ro);
        addDependency.setVisibility(ro ? View.INVISIBLE : View.VISIBLE);
        addResources.setEnabled(!ro);
        addResources.setVisibility(ro ? View.INVISIBLE : View.VISIBLE);
        for(DependencyContainer dependecy : dependencies)
            dependecy.setEnabled(!ro);
        for (ResourcesContainer resource : resources)
            resource.setEnabled(!ro);
        if (ro)
            editSave.setText("Edit");
        else
            editSave.setText("Save");
    }

    private void initView()
    {
        if (dependencies == null)
            dependencies = new ArrayList<>();
        if (resources == null)
            resources = new ArrayList<>();
        editSave = (Button) findViewById(R.id.save);
        title = (TextView) findViewById(R.id.etxt_title);
        description = (TextView) findViewById(R.id.etxt_description);
        btn_duedate = (Button) findViewById(R.id.btn_duedate);
        btn_startdate = (Button) findViewById(R.id.btn_startdate);
        addDependency = (ImageButton) findViewById(R.id.btn_adddependency);
        addResources = (ImageButton) findViewById(R.id.btn_addresource);
        done = (TextView) findViewById(R.id.etxt_done);
        isMilestone = (CheckBox) findViewById(R.id.is_milestone);
        dateFormatter = android.text.format.DateFormat.getDateFormat(getApplicationContext());
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
                editTaskContent = task;
                isTaskFetched = true;
                title.setText(task.getTitle());
                description.setText(task.getDescription());
                btn_duedate.setText(dateFormatter.format(task.getEndDate()));
                btn_startdate.setText(dateFormatter.format(task.getStartDate()));
                done.setText(String.valueOf(task.getAccomplishedPercent()));
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
                for (TaskUser user : task.getUsers())
                    ((LinearLayout) findViewById(R.id.ll_resources)).addView(addResourceDisplay(user.getFirstname() + " " + user.getLastname(), user.getId()).resourceView);
            }
        });
        task.execute(taskID);
        editSave.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (curr_ro)
                {
                    setReadOnly(false);
                    return;
                }
                setReadOnly(true);
                //TODO : save task (linkAPI)
            }
        });
        TaskDetailActivity currentActivity = this;

    }

    private void onCreateNewMode(Intent infos)
    {
        initView();
        setReadOnly(false);
        editTaskContent = new Task();
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
        TaskDetailActivity currentActivity = this;


        if (infos.getCategories().contains(EDIT_MODE))
            onCreateEditMode(infos);
        else
            onCreateNewMode(infos);

        //Init date pickers for both modes
        btn_startdate.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Date date = editTaskContent.getStartDate();
                Calendar cal = GregorianCalendar.getInstance();
                cal.setTime(date);
                DatePickerDialog dialog = new DatePickerDialog(currentActivity, new DatePickerDialog.OnDateSetListener() {
                    @Override
                    public void onDateSet(DatePicker view, int year, int monthOfYear, int dayOfMonth) {
                        Calendar setcal = GregorianCalendar.getInstance();
                        setcal.set(year, monthOfYear, dayOfMonth);
                        if (mode.equals(EDIT_MODE) && setcal.getTime().getTime() > editTaskContent.getEndDate().getTime())
                        {
                            long duration = editTaskContent.getEndDate().getTime() - editTaskContent.getStartDate().getTime();
                            editTaskContent.setEndDate(new Date(setcal.getTime().getTime() + duration));
                            btn_duedate.setText(dateFormatter.format(editTaskContent.getEndDate()));
                        }
                        editTaskContent.setStartDate(setcal.getTime());
                        btn_startdate.setText(dateFormatter.format(setcal.getTime()));
                    }
                }, cal.get(Calendar.YEAR), cal.get(Calendar.MONTH), cal.get(Calendar.DAY_OF_MONTH));
                dialog.show();
            }
        });

        btn_duedate.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Date date = editTaskContent.getEndDate();
                Calendar cal = GregorianCalendar.getInstance();
                cal.setTime(date);
                DatePickerDialog dialog = new DatePickerDialog(currentActivity, new DatePickerDialog.OnDateSetListener() {
                    @Override
                    public void onDateSet(DatePicker view, int year, int monthOfYear, int dayOfMonth) {
                        Calendar setcal = GregorianCalendar.getInstance();
                        setcal.set(year, monthOfYear, dayOfMonth);
                        if (mode.equals(EDIT_MODE) &&setcal.getTime().getTime() < editTaskContent.getStartDate().getTime())
                        {
                            long duration = editTaskContent.getEndDate().getTime() - editTaskContent.getStartDate().getTime();
                            editTaskContent.setStartDate(new Date(setcal.getTime().getTime() - duration));
                            btn_startdate.setText(dateFormatter.format(editTaskContent.getStartDate()));
                        }
                        editTaskContent.setEndDate(setcal.getTime());
                        btn_duedate.setText(dateFormatter.format(setcal.getTime()));
                    }
                }, cal.get(Calendar.YEAR), cal.get(Calendar.MONTH), cal.get(Calendar.DAY_OF_MONTH));
                dialog.show();
            }
        });

        //Add Predecessor link
        addDependency.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent intent = new Intent(getBaseContext(), GanttActivity.class);

                currentActivity.startActivityForResult(intent, 1);
            }
        });

        //Add resource link
        addResources.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                GetAllProjectUsersTask task = new GetAllProjectUsersTask(currentActivity, new GetAllProjectUsersTask.APIGetAllProjectUserListener() {
                    @Override
                    public void onUsersFetched(ArrayList<TaskUser> users) {
                        AlertDialog.Builder builder = new AlertDialog.Builder(currentActivity);
                        ArrayList<TaskUser> selection = new ArrayList<TaskUser>();
                        for (TaskUser usr : users)
                            for (ResourcesContainer res : resources)
                                if (res.ID == usr.getId())
                                {
                                    users.remove(usr);
                                    break;
                                }
                        ResourcesAdapter adapter = new ResourcesAdapter(currentActivity, R.layout.adapter_resource_layout, users);
                        builder.setAdapter(adapter, new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialog, int which) {
                            }
                        });
                        builder.setPositiveButton(getString(R.string.positive_response), new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialog, int which) {
                                for (TaskUser selected : adapter.selection)
                                    ((LinearLayout) findViewById(R.id.ll_resources)).addView(currentActivity.addResourceDisplay(selected.getFirstname() + " " + selected.getLastname(), selected.getId()).resourceView);
                                dialog.dismiss();
                            }
                        });
                        builder.setNegativeButton(getString(R.string.negative_response), new DialogInterface.OnClickListener() {
                            @Override
                            public void onClick(DialogInterface dialog, int which) {
                                dialog.cancel();
                                dialog.dismiss();
                            }
                        });
                        builder.show();
                    }
                });
                task.execute();
            }
        });
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        Log.e("TASK", "CALLED");
        if (resultCode == Activity.RESULT_OK)
        {
            String taskID = data.getStringExtra(Task.INTENT_TASK_ID);
            for (DependencyContainer dep : dependencies)
            {
                if (dep.ID.equals(taskID))
                {
                    AlertDialog.Builder build = new AlertDialog.Builder(this);
                    String reason = "You can't add this dependency. Reason : You try to finish a dependency on the starting task";

                    build.setMessage(reason);
                    return;
                }

            }
            GetTaskInformationsTask task = new GetTaskInformationsTask(this, new GetTaskInformationsTask.APIGetTaskInformationListener() {
                @Override
                public void onTaskFetched(Task task) {
                    ((LinearLayout)findViewById(R.id.ll_dependencies)).addView(addDependencyDisplay(task.getTitle(), task.getId(), Task.ELinkType.END_TO_START).dependencyView);
                }
            });
            task.execute(data.getStringExtra(taskID));
        }
    }
}
