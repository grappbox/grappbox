package com.grappbox.grappbox.grappbox.BugTracker;

import android.os.Bundle;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.grappbox.Model.LoadingFragment;
import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;

public class BugOpenListFragment extends LoadingFragment implements GetLastTicketsTask.LastTicketTaskListener {
    private BugListAdapter bugListAdapter;
    public SwipeRefreshLayout.OnRefreshListener refresher;
    private BugTrackerFragment _parent;
    private int offset = 6;
    public BugOpenListFragment() {
        // Required empty public constructor
    }

    public static BugTrackerFragment newInstance() {
        BugTrackerFragment fragment = new BugTrackerFragment();
        Bundle args = new Bundle();
        return fragment;
    }

    public BugOpenListFragment SetParent(BugTrackerFragment fragment)
    {
        _parent = fragment;
        return this;
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
    }



    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        // Inflate the layout for this fragment
        BugOpenListFragment currentFrag = this;
        View v = inflater.inflate(R.layout.fragment_bug_open_list, container, false);
        SwipeRefreshLayout swiper = (SwipeRefreshLayout) v.findViewById(R.id.pull_refresher);
        startLoading(v, R.id.loader, swiper);
        RecyclerView bugListView = (RecyclerView) swiper.findViewById(R.id.lv_buglist);
        final LinearLayoutManager layoutManager = new LinearLayoutManager(getContext());
        layoutManager.setOrientation(LinearLayoutManager.VERTICAL);
        bugListView.setLayoutManager(layoutManager);
        refresher = new SwipeRefreshLayout.OnRefreshListener() {
            @Override
            public void onRefresh() {
                GetLastTicketsTask task = new GetLastTicketsTask(getActivity(), bugListAdapter, true, 0, 20);
                task.SetRefreshSwiper(swiper);
                offset = 21;
                task.execute();
            }
        };
        swiper.setOnRefreshListener(refresher);
        if (bugListAdapter == null)
            bugListAdapter = new BugListAdapter(_parent, new ArrayList<>(), bugListView);
        bugListAdapter.setListener(new BugListAdapter.BugListListener() {
            @Override
            public void onLoadMore() {
                GetLastTicketsTask task = new GetLastTicketsTask(getActivity(), bugListAdapter, false, offset, offset + 20, true);
                task.execute();
                offset += 20;
            }
        });
        bugListView.setAdapter(bugListAdapter);
        GetLastTicketsTask task = new GetLastTicketsTask(getActivity(), bugListAdapter, true, 0, 20);
        task.SetListener(this);
        task.execute();

        return v;
    }

    @Override
    public void finished() {
        endLoading();
    }
}
