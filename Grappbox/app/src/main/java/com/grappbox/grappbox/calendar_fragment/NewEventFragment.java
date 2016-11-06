package com.grappbox.grappbox.calendar_fragment;

import android.content.DialogInterface;
import android.database.Cursor;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.app.LoaderManager;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.support.v7.app.AlertDialog;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.CalendarListProjectAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.CalendarProjectModel;
import com.grappbox.grappbox.singleton.Session;

import org.w3c.dom.Text;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by tan_f on 04/11/2016.
 */

public class NewEventFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor>{

    private static final String LOG_TAG = NewEventFragment.class.getSimpleName();

    private static int  LOAD_PROJECT = 0;
    private static int  LOAD_USERS = 1;

    private RecyclerView mParticipantsRecycler;
    private LinearLayout mChangeProject;
    private TextView mProjectName;
    private CalendarListProjectAdapter mProjectAdapter;

    private List<CalendarProjectModel> mProjectList;

    private NewEventFragment mFragment = this;

    @Nullable
    @Override
    public View onCreateView(final LayoutInflater inflater, @Nullable final ViewGroup container, @Nullable Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_new_event, container, false);
        mParticipantsRecycler = (RecyclerView) v.findViewById(R.id.event_participants);
        mChangeProject = (LinearLayout) v.findViewById(R.id.project_btn);
        mProjectName = (TextView) v.findViewById(R.id.event_project_name);
        mProjectAdapter = new CalendarListProjectAdapter(getActivity());
        mChangeProject.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
                builder.setTitle(R.string.change_project);
                final List<CalendarProjectModel> dataset = mProjectAdapter.getDataSet();
                builder.setItems(CalendarProjectModel.toStringArray(dataset.toArray(new CalendarProjectModel[dataset.size()])), new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        CalendarProjectModel model = mProjectAdapter.getItemAt(which);
                        mProjectName.setText(model._projectName);
                    }
                });
                builder.show();
            }
        });
        getLoaderManager().initLoader(LOAD_PROJECT, null, mFragment);
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        String accountName = Session.getInstance(getContext()).getCurrentAccount().name;
        if (id == LOAD_PROJECT) {
            CursorLoader cursor = new CursorLoader(getContext(),
                    GrappboxContract.ProjectEntry.buildWithoint(),
                    new String[]{GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry._ID,
                            GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID,
                            GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry.COLUMN_NAME},
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL + "=?",
                    new String[]{String.valueOf(accountName)},
                    null);
            return cursor;
        } else if (id == LOAD_USERS){
            CursorLoader cursor = new CursorLoader(getContext(),
                    GrappboxContract.UserEntry.buildUserWithProject(),
                    new String[]{GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID,
                            GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID,
                            GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_FIRSTNAME,
                            GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_LASTNAME},
                    GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL + "=?",
                    new String[]{String.valueOf(accountName)},
                    null);
            return cursor;
        }
        return null;
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (loader.getId() == LOAD_PROJECT) {
            if (data != null && data.moveToFirst())
            {
                List<CalendarProjectModel> project = new ArrayList<>();
                do {
                    project.add(new CalendarProjectModel(data));
                    Log.v(LOG_TAG, data.getString(data.getColumnIndex(GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry.COLUMN_NAME)));
                } while (data.moveToNext());
                mProjectAdapter.setItem(project);
            }
        }
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {

    }
}
