package com.grappbox.grappbox.grappbox.BugTracker;

import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;

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
        RecyclerView bugListView = (RecyclerView) swiper.findViewById(R.id.lv_buglist);
        final LinearLayoutManager layoutManager = new LinearLayoutManager(getContext());
        layoutManager.setOrientation(LinearLayoutManager.VERTICAL);
        bugListView.setLayoutManager(layoutManager);
        refresher = new SwipeRefreshLayout.OnRefreshListener() {
            @Override
            public void onRefresh() {
                GetLastTicketsTask task = new GetLastTicketsTask(getActivity(), bugListAdapter, true, 0, 2000, true);
                task.SetRefreshSwiper(swiper);
                task.execute();
            }
        };
        swiper.setOnRefreshListener(refresher);
        if (bugListAdapter == null)
            bugListAdapter = new BugListAdapter(_parent, new ArrayList<>());
        bugListView.setAdapter(bugListAdapter);
        GetLastTicketsTask task = new GetLastTicketsTask(getActivity(), bugListAdapter, true, 0, 2000, true);
        task.execute();

        return v;
    }
}
