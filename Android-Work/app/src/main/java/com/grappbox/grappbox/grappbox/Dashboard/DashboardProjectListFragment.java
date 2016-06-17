package com.grappbox.grappbox.grappbox.Dashboard;


import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import com.grappbox.grappbox.grappbox.Model.LoadingFragment;
import com.grappbox.grappbox.grappbox.Model.ProjectModel;
import com.grappbox.grappbox.grappbox.Model.SessionAdapter;
import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;


/**
 * Created by tan_f on 14/06/2016.
 */
public class DashboardProjectListFragment extends LoadingFragment {

    private DashboardProjectListFragment _context = this;
    private RecyclerView _recycleView;
    private RecyclerView.LayoutManager _layoutManager;
    private SwipeRefreshLayout _swiper;
    private DashboardRVAdapter _adapter = null;
    public SwipeRefreshLayout.OnRefreshListener _refresher;

    public void onCreate(Bundle savedInstanceBundle)
    {
        super.onCreate(savedInstanceBundle);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        View rootView = inflater.inflate(R.layout.fragment_project_list, container, false);
        _swiper = (SwipeRefreshLayout) rootView.findViewById(R.id.pull_refresher);
        startLoading(rootView, R.id.loader, _swiper);

        _recycleView = (RecyclerView)rootView.findViewById(R.id.project_list);
        _recycleView.setHasFixedSize(true);
        _layoutManager = new LinearLayoutManager(rootView.getContext());
        _recycleView.setLayoutManager(_layoutManager);

        _refresher = new SwipeRefreshLayout.OnRefreshListener() {
            @Override
            public void onRefresh() {
                APIRequestGetProjectList api = new APIRequestGetProjectList(_context, _adapter, true);
                api.SetRefreshSwiper(_swiper);
                api.execute();
            }
        };
        if (_adapter == null){
            _adapter = new DashboardRVAdapter(this, new ArrayList<>(), _recycleView);
        }
        _recycleView.setAdapter(_adapter);
        _adapter.setListener(new DashboardRVAdapter.ProjectListListenner() {

            @Override
            public void onItemClick(ProjectModel project)
            {
                SessionAdapter.getInstance().setCurrentSelectedProject(project.getId());
                SessionAdapter.getInstance().setCurrentSelectedProjectName(project.getName());
                FragmentManager fm = getFragmentManager();
                if (fm != null) {
                    Fragment fragment = new DashboardFragment();
                    fm.beginTransaction().replace(R.id.content_frame, fragment).commit();
                }
            }
        });

        APIRequestGetProjectList api = new APIRequestGetProjectList(_context, _adapter, true);
        api.execute();
        return rootView;
    }

    public void fillView()
    {
        endLoading();
    }
}
