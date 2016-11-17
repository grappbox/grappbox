package com.grappbox.grappbox.calendar_fragment;

import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
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
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.CalendarListProjectAdapter;
import com.grappbox.grappbox.adapter.CalendarParticipantAdapter;
import com.grappbox.grappbox.data.GrappboxContract;
import com.grappbox.grappbox.model.CalendarEventModel;
import com.grappbox.grappbox.model.CalendarProjectModel;
import com.grappbox.grappbox.model.UserModel;
import com.grappbox.grappbox.receiver.CalendarEventReceiver;
import com.grappbox.grappbox.singleton.Session;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by tan_f on 04/11/2016.
 */

public class NewEventFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor>, NewEventActivity.OnEventSaveData{

    private static final String LOG_TAG = NewEventFragment.class.getSimpleName();

    private static int  LOAD_PROJECT = 0;
    private static int  LOAD_USERS = 1;

    private RecyclerView mParticipantsRecycler;

    private TextView mProjectName;
    private CalendarListProjectAdapter mProjectAdapter;
    private CalendarParticipantAdapter mParticipantAdapter;

    private List<UserModel> mExistingParticipants;

    private NewEventFragment mFragment = this;
    private long mProjectSelected = -1;

    public NewEventFragment() {
    }


    @Nullable
    @Override
    public View onCreateView(final LayoutInflater inflater, @Nullable final ViewGroup container, @Nullable Bundle savedInstanceState) {
        View v = inflater.inflate(R.layout.fragment_new_event, container, false);
        mParticipantsRecycler = (RecyclerView) v.findViewById(R.id.event_participants);
        mParticipantAdapter = new CalendarParticipantAdapter(getActivity());
        mProjectName = (TextView) v.findViewById(R.id.event_project_name);
        mProjectAdapter = new CalendarListProjectAdapter(getActivity());
        mProjectAdapter.setItem(null);
        mProjectName.setText(mProjectAdapter.getItemAt(0)._projectName);

        mParticipantsRecycler.setLayoutManager(new LinearLayoutManager(getActivity(), LinearLayoutManager.VERTICAL, false));
        mParticipantsRecycler.setAdapter(mParticipantAdapter);

        v.findViewById(R.id.project_btn).setOnClickListener(new OnChangeProject(getActivity()));
        v.findViewById(R.id.participant_btn).setOnClickListener(new OnChangeParticipant(getActivity()));

        return v;
    }

    @Override
    public void onEventSave(String title, String desc, String begin, String end) {
        ArrayList<Long> idGrappboxList = new ArrayList<>();
        for (UserModel model : mParticipantAdapter.getDataSet()) {
            idGrappboxList.add(model._id);
        }
        Intent save = new Intent(getActivity(), GrappboxJustInTimeService.class);
        Bundle apiPar = new Bundle();
        apiPar.putSerializable(GrappboxJustInTimeService.EXTRA_ADD_PARTICIPANT, idGrappboxList);
        save.setAction(GrappboxJustInTimeService.ACTION_CREATE_EVENT);
        save.putExtra(GrappboxJustInTimeService.EXTRA_TITLE, title);
        save.putExtra(GrappboxJustInTimeService.EXTRA_DESCRIPTION, desc);
        save.putExtra(GrappboxJustInTimeService.EXTRA_CALENDAR_EVENT_BEGIN, begin);
        save.putExtra(GrappboxJustInTimeService.EXTRA_CALENDAR_EVENT_END, end);
        save.putExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, mProjectSelected);
        save.putExtra(GrappboxJustInTimeService.EXTRA_BUNDLE, apiPar);
        getActivity().startService(save);
    }

    @Override
    public void onAttach(Context context) {
        super.onAttach(context);
    }

    @Override
    public void onActivityCreated(@Nullable Bundle savedInstanceState) {
        super.onActivityCreated(savedInstanceState);
        getActivity().getSupportLoaderManager().initLoader(LOAD_PROJECT, null, mFragment);
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        String accountName = Session.getInstance(getContext()).getCurrentAccount().name;
        String sort = null;
        if (id == LOAD_PROJECT) {
            final String[] projection = {GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry._ID,
                    GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry.COLUMN_GRAPPBOX_ID,
                    GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry.COLUMN_NAME};
            final String selection = GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL + "=?";
            final String[] arguments = {String.valueOf(accountName)};
            return new CursorLoader(getContext(), GrappboxContract.ProjectEntry.buildWithoint(), projection, selection, arguments, sort);
        } else if (id == LOAD_USERS){
            if (mProjectSelected != -1) {
                final String[] projection = {GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry._ID,
                        GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_GRAPPBOX_ID,
                        GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_FIRSTNAME,
                        GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_LASTNAME,
                        GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_DATE_BIRTHDAY_UTC,
                        GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_URI_AVATAR,
                        GrappboxContract.UserEntry.TABLE_NAME + "." + GrappboxContract.UserEntry.COLUMN_CONTACT_EMAIL};
                final String  selection = GrappboxContract.ProjectEntry.TABLE_NAME + "." + GrappboxContract.ProjectEntry._ID + "=?";
                String[] arguments = {String.valueOf(mProjectSelected)};
                return new CursorLoader(getContext(), GrappboxContract.UserEntry.buildUserWithProject(), projection, selection, arguments, sort);
            } else {
                mExistingParticipants.clear();
            }
        }
        return null;
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        if (data == null || !data.moveToFirst())
            return;

        if (loader.getId() == LOAD_PROJECT) {
            List<CalendarProjectModel> project = new ArrayList<>();
            do {
                project.add(new CalendarProjectModel(data));
            } while (data.moveToNext());
            mProjectAdapter.setItem(project);
        } else if (loader.getId() == LOAD_USERS) {
            if (mExistingParticipants == null)
                mExistingParticipants = new ArrayList<>();
            mExistingParticipants.clear();
            do {
                mExistingParticipants.add(new UserModel(data));
            } while (data.moveToNext());
        }
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {
        loader.forceLoad();
    }

    private class OnChangeProject implements View.OnClickListener {
        private Context mContext;

        public OnChangeProject(Context context) {
            mContext = context;
        }

        @Override
        public void onClick(View v) {
            AlertDialog.Builder builder = new AlertDialog.Builder(mContext, R.style.CaledarDialogOverride);
            builder.setTitle(R.string.change_project);
            final List<CalendarProjectModel> dataset = mProjectAdapter.getDataSet();
            builder.setItems(CalendarProjectModel.toStringArray(dataset.toArray(new CalendarProjectModel[dataset.size()])), new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    CalendarProjectModel model = mProjectAdapter.getItemAt(which);
                    mProjectName.setText(model._projectName);
                    mProjectSelected = model._localProjectId;
                    mParticipantAdapter.clear();
                    getLoaderManager().restartLoader(LOAD_USERS, null, mFragment);
                }
            });
            builder.show();
        }
    }

    private class OnChangeParticipant implements View.OnClickListener {
        private Context mContext;

        public OnChangeParticipant(Context context) {
            mContext = context;
        }

        @Override
        public void onClick(View v) {
            AlertDialog.Builder builder = new AlertDialog.Builder(mContext, R.style.CaledarDialogOverride);
            builder.setTitle(R.string.set_participants);

            if (mExistingParticipants == null || mExistingParticipants.size() == 0)
                return;
            boolean[] checkedUsers = new boolean[mExistingParticipants.size()];
            final List<UserModel> dataset = mParticipantAdapter.getDataSet();
            final List<UserModel> selected = new ArrayList<>();

            for (int i = 0; i < checkedUsers.length; ++i) {
                UserModel model = mExistingParticipants.get(i);
                checkedUsers[i] = false;
                for (int j = 0; j < dataset.size(); j++) {
                    if (model.equals(dataset.get(j))){
                        checkedUsers[i] = true;
                        selected.add(model);
                        break;
                    }
                }
            }
            builder.setMultiChoiceItems(UserModel.toStringArray(mExistingParticipants.toArray(new UserModel[mExistingParticipants.size()])), checkedUsers, new DialogInterface.OnMultiChoiceClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which, boolean isChecked) {
                    UserModel model = mExistingParticipants.get(which);
                    if (isChecked) {
                        selected.add(model);
                    } else {
                        selected.remove(model);
                    }
                }
            });
            builder.setPositiveButton(R.string.positive_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    mParticipantAdapter.setDataSet(selected);
                }
            });
            builder.setNegativeButton(R.string.negative_response, new DialogInterface.OnClickListener() {
                @Override
                public void onClick(DialogInterface dialog, int which) {
                    dialog.cancel();
                }
            });
            builder.show();
        }
    }
}
