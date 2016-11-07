/*
 * Created by Marc Wieser on 5/11/2016
 * If you have any problem or question about this work
 * please contact the author at marc.wieser@gmail.com
 *
 * The following code is owned by GrappBox you can't
 * use it without any authorization or special instructions
 * GrappBox © 2016
 */

package com.grappbox.grappbox;

import android.app.Fragment;
import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.support.v7.app.AlertDialog;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.grappbox.grappbox.model.RoleModel;
import com.grappbox.grappbox.sync.GrappboxJustInTimeService;

import java.util.HashMap;
import java.util.Map;

/**
 * A placeholder fragment containing a simple view.
 */
public class RoleEditActivityFragment extends Fragment implements View.OnClickListener, RoleEditActivity.SaveCallback {
    private TextView roleWhiteboard, roleBugtracker, roleCalendar, roleTask, roleCloud, roleTeamTimeline, roleCustomerTimeline, roleGantt, roleProjectSettings;
    private LinearLayout whiteboard, bugtracker, calendar, task, cloud, teamTimeline, customerTimeline, gantt, projectSettings;
    private TextView title;
    private RoleModel model = null;
    private boolean isNew;

    public RoleEditActivityFragment() {
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        isNew = getActivity().getIntent() == null || getActivity().getIntent().getAction().equals(RoleEditActivity.ACTION_NEW);
        if (!isNew)
            model = getActivity().getIntent().getParcelableExtra(RoleEditActivity.EXTRA_MODEL);
        else
            model = new RoleModel();
        View v = inflater.inflate(R.layout.fragment_role_edit, container, false);
        roleWhiteboard = (TextView) v.findViewById(R.id.role_whiteboard);
        roleBugtracker = (TextView) v.findViewById(R.id.role_bugtracker);
        roleCalendar = (TextView) v.findViewById(R.id.role_calendar);
        roleTask = (TextView) v.findViewById(R.id.role_task);
        roleTeamTimeline = (TextView) v.findViewById(R.id.role_team_timeline);
        roleCloud = (TextView) v.findViewById(R.id.role_cloud);
        roleCustomerTimeline = (TextView) v.findViewById(R.id.role_customer_timeline);
        roleGantt = (TextView) v.findViewById(R.id.role_gantt);
        roleProjectSettings = (TextView) v.findViewById(R.id.role_project_settings);
        title = (TextView) v.findViewById(R.id.title);
        whiteboard = (LinearLayout) v.findViewById(R.id.click_whiteboard);
        bugtracker = (LinearLayout) v.findViewById(R.id.click_bugtracker);
        calendar = (LinearLayout) v.findViewById(R.id.click_calendar);
        task = (LinearLayout) v.findViewById(R.id.click_task);
        cloud = (LinearLayout) v.findViewById(R.id.click_cloud);
        teamTimeline = (LinearLayout) v.findViewById(R.id.click_team_timeline);
        customerTimeline = (LinearLayout) v.findViewById(R.id.click_customer_timeline);
        gantt = (LinearLayout) v.findViewById(R.id.click_gantt);
        projectSettings = (LinearLayout) v.findViewById(R.id.click_project_settings);
        String[] types = getActivity().getResources().getStringArray(R.array.access_types);
        roleWhiteboard.setText(types[model.whiteboardAccess]);
        roleBugtracker.setText(types[model.bugtrackerAccess]);
        roleCalendar.setText(types[model.eventAccess]);
        roleTask.setText(types[model.taskAccess]);
        roleTeamTimeline.setText(types[model.teamTimelineAccess]);
        roleCloud.setText(types[model.cloudAccess]);
        roleCustomerTimeline.setText(types[model.customerTimelineAccess]);
        roleGantt.setText(types[model.ganttAccess]);
        roleProjectSettings.setText(types[model.projectSettingsAccess]);
        title.setText(model.name);
        whiteboard.setTag(R.id.TAG_ID, R.string.whiteboard);
        whiteboard.setTag(R.id.TAG_NAME, getActivity().getString(R.string.whiteboard));
        bugtracker.setTag(R.id.TAG_ID, R.string.bugtracker);
        bugtracker.setTag(R.id.TAG_NAME, getActivity().getString(R.string.bugtracker));
        calendar.setTag(R.id.TAG_ID, R.string.calendar);
        calendar.setTag(R.id.TAG_NAME, getActivity().getString(R.string.calendar));
        task.setTag(R.id.TAG_ID, R.string.task);
        task.setTag(R.id.TAG_NAME, getActivity().getString(R.string.task));
        cloud.setTag(R.id.TAG_ID, R.string.cloud);
        cloud.setTag(R.id.TAG_NAME, getActivity().getString(R.string.cloud));
        teamTimeline.setTag(R.id.TAG_ID, R.string.team_timeline);
        teamTimeline.setTag(R.id.TAG_NAME, getActivity().getString(R.string.team_timeline));
        customerTimeline.setTag(R.id.TAG_ID, R.string.customer_timeline);
        customerTimeline.setTag(R.id.TAG_NAME, getActivity().getString(R.string.customer_timeline));
        gantt.setTag(R.id.TAG_ID, R.string.gantt);
        gantt.setTag(R.id.TAG_NAME, getActivity().getString(R.string.gantt));
        projectSettings.setTag(R.id.TAG_ID, R.string.project_settings);
        projectSettings.setTag(R.id.TAG_NAME, getActivity().getString(R.string.project_settings));
        whiteboard.setTag(R.id.TAG_ACCESS_VIEWER, roleWhiteboard);
        bugtracker.setTag(R.id.TAG_ACCESS_VIEWER, roleBugtracker);
        calendar.setTag(R.id.TAG_ACCESS_VIEWER, roleCalendar);
        task.setTag(R.id.TAG_ACCESS_VIEWER, roleTask);
        cloud.setTag(R.id.TAG_ACCESS_VIEWER, roleCloud);
        teamTimeline.setTag(R.id.TAG_ACCESS_VIEWER, roleTeamTimeline);
        customerTimeline.setTag(R.id.TAG_ACCESS_VIEWER, roleCustomerTimeline);
        gantt.setTag(R.id.TAG_ACCESS_VIEWER,  roleGantt);
        projectSettings.setTag(R.id.TAG_ACCESS_VIEWER, roleProjectSettings);

        whiteboard.setOnClickListener(this);
        bugtracker.setOnClickListener(this);
        calendar.setOnClickListener(this);
        task.setOnClickListener(this);
        cloud.setOnClickListener(this);
        teamTimeline.setOnClickListener(this);
        customerTimeline.setOnClickListener(this);
        gantt.setOnClickListener(this);
        projectSettings.setOnClickListener(this);
        if (getActivity() instanceof RoleEditActivity){
            ((RoleEditActivity) getActivity()).registerSaveCallback(this);
        }
        return v;
    }

    private void editModelAccess(int tag, int access){
        switch (tag) {
            case R.string.whiteboard:
                model.whiteboardAccess = access;
                break;
            case R.string.bugtracker:
                model.bugtrackerAccess = access;
                break;
            case R.string.calendar:
                model.eventAccess = access;
                break;
            case R.string.task:
                model.taskAccess = access;
                break;
            case R.string.cloud:
                model.cloudAccess = access;
                break;
            case R.string.team_timeline:
                model.teamTimelineAccess = access;
                break;
            case R.string.customer_timeline:
                model.customerTimelineAccess = access;
                break;
            case R.string.gantt:
                model.ganttAccess = access;
                break;
            case R.string.project_settings:
                model.projectSettingsAccess = access;
                break;
        }
    }

    @Override
    public void onClick(final View v) {
        AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
        builder.setTitle((String) v.getTag(R.id.TAG_NAME));
        builder.setItems(getResources().getStringArray(R.array.access_types), new DialogInterface.OnClickListener() {
            @Override
            public void onClick(DialogInterface dialog, int which) {
                ((TextView)v.getTag(R.id.TAG_ACCESS_VIEWER)).setText(getResources().getStringArray(R.array.access_types)[which]);
                editModelAccess((int) v.getTag(R.id.TAG_ID), which);
                dialog.dismiss();
            }
        });
        builder.show();
    }

    @Override
    public void onSave() {
        Intent save = new Intent(getActivity(), GrappboxJustInTimeService.class);
        Bundle args = new Bundle();
        if (isNew){
            save.setAction(GrappboxJustInTimeService.ACTION_CREATE_ROLE);
            save.addCategory(GrappboxJustInTimeService.CATEGORY_NEW);
            args.putLong("_id", getActivity().getIntent().getLongExtra(GrappboxJustInTimeService.EXTRA_PROJECT_ID, -1));
        } else {
            save.setAction(GrappboxJustInTimeService.ACTION_UPDATE_ROLE);
            args.putLong("_id", model._id);
        }
        args.putString("name", title.getText().toString());
        args.putInt("teamTimeline", model.teamTimelineAccess);
        args.putInt("customerTimeline", model.customerTimelineAccess);
        args.putInt("gantt", model.ganttAccess);
        args.putInt("whiteboard", model.whiteboardAccess);
        args.putInt("bugtracker", model.bugtrackerAccess);
        args.putInt("event", model.eventAccess);
        args.putInt("task", model.taskAccess);
        args.putInt("projectSettings", model.projectSettingsAccess);
        args.putInt("cloud", model.cloudAccess);
        save.putExtra(GrappboxJustInTimeService.EXTRA_BUNDLE, args);
        getActivity().startService(save);
    }

    @Override
    public void onDelete() {
        Intent delete = new Intent(getActivity(), GrappboxJustInTimeService.class);
        delete.setAction(GrappboxJustInTimeService.ACTION_DELETE_ROLE);
        delete.putExtra(GrappboxJustInTimeService.EXTRA_ROLE_ID, model._id);
        getActivity().startService(delete);
    }
}
