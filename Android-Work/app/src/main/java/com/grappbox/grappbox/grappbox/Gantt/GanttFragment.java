package com.grappbox.grappbox.grappbox.Gantt;


import android.content.pm.ActivityInfo;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;

/**
 * A simple {@link Fragment} subclass.
 */
public class GanttFragment extends Fragment {


    public GanttFragment() {
        // Required empty public constructor
    }

    @Override
    public void onPause() {
        super.onPause();
        getActivity().setRequestedOrientation(ActivityInfo.SCREEN_ORIENTATION_UNSPECIFIED);
    }

    private class TaskListener implements GanttChart.GanttTaskListener {
        @Override
        public void onTaskClick(Task task) {
            
        }
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_gantt, container, false);

        getActivity().setTitle("GANTT");
        GanttChart ganttView = (GanttChart) v.findViewById(R.id.ganttchart);
        APIGetAllTask gettaskTask = new APIGetAllTask(getActivity(), new APIGetAllTask.APIGetAllTaskListener() {
            @Override
            public void onTaskFetched(ArrayList<Task> tasks) {
                ganttView.SetTasks(tasks);
                ganttView.setTaskListener(new TaskListener());
            }
        });
        gettaskTask.execute(String.valueOf(SessionAdapter.getInstance().getCurrentSelectedProject()));
        return v;
    }

}
