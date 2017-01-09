package com.grappbox.grappbox.project_fragments;


import android.database.Cursor;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.TaskModel;
import com.grappbox.grappbox.views.GanttChart;

import java.util.ArrayList;
import java.util.List;

/**
 * A simple {@link Fragment} subclass.
 */
public class GanttFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor> {
    private static final int LOADER_TASKS = 0;
    private static final String LOG_TAG = GanttFragment.class.getSimpleName();

    private GanttChart gantt;

    public GanttFragment() {
        // Required empty public constructor
    }


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_gantt, container, false);

        gantt = (GanttChart) v.findViewById(R.id.gantt);
        getLoaderManager().initLoader(LOADER_TASKS, null, this);
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        if (id == LOADER_TASKS){
            long projectId = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);
            return new CursorLoader(getActivity(), GrappboxContract.TaskEntry.CONTENT_URI, TaskModel.projection, GrappboxContract.TaskEntry.TABLE_NAME + "." + GrappboxContract.TaskEntry.COLUMN_LOCAL_PROJECT+"=?", new String[]{String.valueOf(projectId)}, null);
        }
        return null;
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        Log.e(LOG_TAG, String.valueOf(data.getCount()));
        if (!data.moveToFirst())
            return;
        if (loader.getId() == LOADER_TASKS){
            List<TaskModel> tasks = new ArrayList<>();

            do{
                tasks.add(new TaskModel(data, getActivity()));
            } while (data.moveToNext());

            gantt.SetTasks(tasks);
        }
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }
}
