package com.grappbox.grappbox.grappbox.BugTracker;

import android.content.Context;
import android.content.Intent;
import android.net.Uri;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.widget.SwipeRefreshLayout;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ListView;

import com.grappbox.grappbox.grappbox.R;

public class BugClosedListFragment extends Fragment {
    private BugListAdapter bugListAdapter;
    private SwipeRefreshLayout swiper;
    public SwipeRefreshLayout.OnRefreshListener refresher;
    private BugTrackerFragment _parent;

    public BugClosedListFragment() {
        // Required empty public constructor
    }

    public static BugTrackerFragment newInstance() {
        BugTrackerFragment fragment = new BugTrackerFragment();
        Bundle args = new Bundle();
        return fragment;
    }

    public BugClosedListFragment SetParent(BugTrackerFragment parent)
    {
        _parent = parent;
        return this;
    }

    public SwipeRefreshLayout GetSwiper()
    {
        return swiper;
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        View v = inflater.inflate(R.layout.fragment_bug_open_list, container, false);
        swiper = (SwipeRefreshLayout) v.findViewById(R.id.pull_refresher);
        ListView bugListView = (ListView) swiper.findViewById(R.id.lv_buglist);
        bugListView.setClickable(true);
        bugListView.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                BugEntity bug = (BugEntity) parent.getItemAtPosition(position);

                if (!bug.IsValid())
                    return;
                Intent intent = new Intent(getContext(), EditBugActivity.class);
                intent.putExtra(EditBugActivity.EXTRA_GRAPPBOX_BUG_ID, bug.GetId());
                startActivity(intent);
            }
        });
        refresher = new SwipeRefreshLayout.OnRefreshListener() {
            @Override
            public void onRefresh() {
                GetLastTicketsTask task = new GetLastTicketsTask(getActivity(), bugListAdapter, true, 0, 20, true);
                task.SetRefreshSwiper(swiper);
                task.execute();
            }
        };
        swiper.setOnRefreshListener(refresher);
        if (bugListAdapter == null)
            bugListAdapter = new BugListAdapter(getContext(), R.layout.lvitem_bug);
        bugListView.setAdapter(bugListAdapter);
        bugListAdapter.SetParentFragment(_parent);
        GetLastTicketsTask task = new GetLastTicketsTask(getActivity(), bugListAdapter, true, 0, 20, true);
        task.execute();

        return v;
    }
}
