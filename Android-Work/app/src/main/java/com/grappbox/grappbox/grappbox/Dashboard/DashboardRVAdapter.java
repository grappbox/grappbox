package com.grappbox.grappbox.grappbox.Dashboard;

import android.support.v7.widget.CardView;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.Model.ProjectModel;
import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by tan_f on 15/06/2016.
 */


public class DashboardRVAdapter extends RecyclerView.Adapter<DashboardRVAdapter.ProjectViewHolder> {

    private DashboardProjectListFragment _context;
    private List<ProjectModel> _projects;
    private ProjectListListenner _eventListener;
    private boolean _isLoading = true;

    public interface ProjectListListenner
    {
        void onItemClick(ProjectModel project);
    }

    public static class ProjectViewHolder extends RecyclerView.ViewHolder {
        CardView _cv;
        TextView _projectName;
        TextView _projectDesc;
        TextView _projectCompany;
        TextView _projectMail;

        ProjectViewHolder(View itemView){
            super(itemView);
            _cv = (CardView)itemView.findViewById(R.id.card_view_project);
            _projectName = (TextView)itemView.findViewById(R.id.dashboard_project_name);
            _projectDesc = (TextView)itemView.findViewById(R.id.dashboard_project_desc);
            _projectCompany = (TextView)itemView.findViewById(R.id.dashboard_project_company);
            _projectMail = (TextView)itemView.findViewById(R.id.dashboard_project_mail);
        }

        public void bind(final ProjectModel model, final ProjectListListenner listener)
        {
            itemView.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    listener.onItemClick(model);
                }
            });
        }

    }



    DashboardRVAdapter(DashboardProjectListFragment context, ArrayList<ProjectModel> dataset, RecyclerView recyclerView){
        _context = context;
        _projects = dataset;

        LinearLayoutManager layoutManager = (LinearLayoutManager) recyclerView.getLayoutManager();
        recyclerView.addOnScrollListener(new RecyclerView.OnScrollListener() {
            @Override
            public void onScrolled(RecyclerView recyclerView, int dx, int dy) {
                super.onScrolled(recyclerView, dx, dy);
            }
        });
    }

    @Override
    public int getItemCount(){
        return _projects.size();
    }

    @Override
    public ProjectViewHolder onCreateViewHolder(ViewGroup viewGroup, int i){
        View v = LayoutInflater.from(viewGroup.getContext()).inflate(R.layout.card_dashboard_projectlist, viewGroup, false);
        ProjectViewHolder projectViewHolder = new ProjectViewHolder(v);
        return projectViewHolder;
    }

    @Override
    public void onBindViewHolder(ProjectViewHolder projectViewHolder, int i){
        projectViewHolder.bind(_projects.get(i), _eventListener);
        projectViewHolder._projectName.setText(_projects.get(i).getName());
        projectViewHolder._projectCompany.setText(_projects.get(i).getCompany());
        projectViewHolder._projectDesc.setText(_projects.get(i).getDescription());
        projectViewHolder._projectMail.setText(_projects.get(i).getContact_mail());

    }

    @Override
    public void onAttachedToRecyclerView(RecyclerView recyclerView){
        super.onAttachedToRecyclerView(recyclerView);
    }

    public void clearData()
    {
        _projects = new ArrayList<>();
        notifyDataSetChanged();
    }

    public void insertData(ProjectModel data, int position)
    {
        if (position == -1)
            position = getItemCount();
        _projects.add(position, data);
        notifyItemInserted(position);
    }

    public void setListener(ProjectListListenner listener)
    {
        _eventListener = listener;
    }
}