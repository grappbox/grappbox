package com.grappbox.grappbox.dashboard_fragment;

import android.support.v4.app.LoaderManager;
import android.support.v4.content.ContextCompat;
import android.support.v4.content.CursorLoader;
import android.support.v4.content.Loader;
import android.database.Cursor;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.support.v4.app.Fragment;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ProgressBar;

import com.grappbox.grappbox.ProjectActivity;
import com.grappbox.grappbox.R;
import com.grappbox.grappbox.adapter.TeamOccupationAdapter;
import com.grappbox.grappbox.data.GrappboxContract.UserEntry;
import com.grappbox.grappbox.data.GrappboxContract.OccupationEntry;
import com.grappbox.grappbox.item_decoration.HorizontalDivider;
import com.grappbox.grappbox.model.OccupationModel;
import com.grappbox.grappbox.receiver.RefreshReceiver;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by tan_f on 02/01/2017.
 */

public class TeamOccupationFragment extends Fragment implements LoaderManager.LoaderCallbacks<Cursor>, SwipeRefreshLayout.OnRefreshListener {

    public static final String LOG_TAG = TeamOccupationFragment.class.getSimpleName();

    public static final int OCCUPATION = 0;

    private TeamOccupationAdapter mAdapter = null;
    private SwipeRefreshLayout mRefresher;
    private RecyclerView mOccupationList;
    private ProgressBar mLoader;

    public static final String[] projectionOccupation = {
            OccupationEntry.TABLE_NAME + "." + OccupationEntry._ID,
            OccupationEntry.TABLE_NAME + "." + OccupationEntry.COLUMN_IS_BUSY,
            OccupationEntry.TABLE_NAME + "." + OccupationEntry.COLUMN_LOCAL_PROJECT_ID,
            OccupationEntry.TABLE_NAME + "." + OccupationEntry.COLUMN_COUNT_TASK_BEGUN,
            OccupationEntry.TABLE_NAME + "." + OccupationEntry.COLUMN_COUNT_TASK_ONGOING,
            UserEntry.TABLE_NAME + "." + UserEntry.COLUMN_FIRSTNAME,
            UserEntry.TABLE_NAME + "." + UserEntry.COLUMN_LASTNAME
    };

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        super.onCreateView(inflater, container, savedInstanceState);
        View v = inflater.inflate(R.layout.fragment_team_occupation, container, false);
        mOccupationList = (RecyclerView)v.findViewById(R.id.team_occupation_list);
        mAdapter = new TeamOccupationAdapter(getActivity());
        mOccupationList.setAdapter(mAdapter);
        mOccupationList.addItemDecoration(new HorizontalDivider(ContextCompat.getColor(getActivity(), R.color.GrappGrayMedium)));
        mOccupationList.setLayoutManager(new LinearLayoutManager(getActivity()));

        mLoader = (ProgressBar)v.findViewById(R.id.loader);
        mRefresher = (SwipeRefreshLayout) v.findViewById(R.id.refresh);
        mRefresher.setOnRefreshListener(this);

        mAdapter.registerAdapterDataObserver(new AdapterObserver());

        if (savedInstanceState != null)
            mRefresher.setVisibility(View.VISIBLE);
        getLoaderManager().initLoader(OCCUPATION, null, this);
        return v;
    }

    @Override
    public Loader<Cursor> onCreateLoader(int id, Bundle args) {
        long lpid = getActivity().getIntent().getLongExtra(ProjectActivity.EXTRA_PROJECT_ID, -1);
        String selection;
        String[] selectionArgs;
        String sortOrder = UserEntry.COLUMN_LASTNAME + " ASC";

        switch (id){
            case OCCUPATION:
                selection = OccupationEntry.TABLE_NAME + "." + OccupationEntry.COLUMN_LOCAL_PROJECT_ID + "=?";
                selectionArgs = new String[]{String.valueOf(lpid)};
                break;

            default:
                throw new IllegalArgumentException("Type doesn't exist");
        }
        return new CursorLoader(getActivity(), OccupationEntry.CONTENT_URI, projectionOccupation, selection, selectionArgs, sortOrder);
    }

    @Override
    public void onLoadFinished(Loader<Cursor> loader, Cursor data) {
        mAdapter.clear();
        if (data == null || !data.moveToFirst())
            return;
        List<OccupationModel> models = new ArrayList<>();
        do {
            models.add(new OccupationModel(data));
        } while (data.moveToNext());
        mAdapter.addAll(models);
    }

    @Override
    public void onLoaderReset(Loader<Cursor> loader) {
        loader.forceLoad();
    }

    class AdapterObserver extends RecyclerView.AdapterDataObserver {

        @Override
        public void onChanged() {
            super.onChanged();
            mLoader.setVisibility(View.GONE);
            mRefresher.setVisibility(View.VISIBLE);
        }
    }

    @Override
    public void onRefresh() {
        getLoaderManager().initLoader(OCCUPATION, null, this);
    }
}
