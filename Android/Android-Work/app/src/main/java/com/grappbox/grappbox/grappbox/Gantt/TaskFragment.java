package com.grappbox.grappbox.grappbox.Gantt;


import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;

/**
 * A simple {@link Fragment} subclass.
 */
public class TaskFragment extends Fragment {
    private RecyclerView recycler;
    private TaskListAdapter adapter;
    private RecyclerView.LayoutManager layoutManager;

    public TaskFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_task, container, false);

        recycler = (RecyclerView) v.findViewById(R.id.tasklist_recycler);
        layoutManager = new LinearLayoutManager(getActivity());
        recycler.setLayoutManager(layoutManager);
        adapter = new TaskListAdapter(null);
        adapter.setInteractionObject(new TaskListAdapter.TaskCardInteraction(){
            @Override
            public void onOpenClicked(String ID) {
                Intent intent = new Intent(getActivity(), TaskDetailActivity.class);

                intent.addCategory(TaskDetailActivity.EDIT_MODE);
                intent.putExtra(Task.INTENT_TASK_ID, ID);
                startActivity(intent);
            }

        });
        recycler.setAdapter(adapter);
        APIGetAllTask task = new APIGetAllTask(getActivity(), new APIGetAllTask.APIGetAllTaskListener() {
            @Override
            public void onTaskFetched(ArrayList<Task> tasks) {
                Task[] taskArray = new Task[tasks.size()];
                Object[] objs = tasks.toArray();
                for (int i = 0; i < tasks.size(); ++i)
                    taskArray[i] = (Task) objs[i];
                adapter.setDataSet(taskArray);
                adapter.notifyDataSetChanged();
            }
        });
        task.execute(String.valueOf(SessionAdapter.getInstance().getCurrentSelectedProject()));
        return v;
    }

}
