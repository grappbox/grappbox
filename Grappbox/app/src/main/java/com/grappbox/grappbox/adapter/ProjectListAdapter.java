package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.database.Cursor;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CursorAdapter;
import android.widget.ImageView;
import android.widget.TextView;

import com.grappbox.grappbox.R;

import static com.grappbox.grappbox.data.GrappboxContract.ProjectEntry;

/**
 * Created by marcw on 06/09/2016.
 */
public class ProjectListAdapter extends CursorAdapter {


    private static class ViewHolder {
        public ImageView logo;
        public TextView projectName, companyName, bugCount, taskCount;

        public ViewHolder(View view) {
            logo = (ImageView) view.findViewById(R.id.logo);
            projectName = (TextView) view.findViewById(R.id.project_name);
            companyName = (TextView) view.findViewById(R.id.company_name);
            bugCount = (TextView) view.findViewById(R.id.bugcount);
            taskCount = (TextView) view.findViewById(R.id.taskcount);
        }
    }

    public ProjectListAdapter(Context context, Cursor c, int flags) {
        super(context, c, flags);
    }

    @Override
    public View newView(Context context, Cursor cursor, ViewGroup parent) {
        View v = LayoutInflater.from(context).inflate(R.layout.list_item_project_choosing, parent, false);
        ViewHolder holder = new ViewHolder(v);
        v.setTag(holder);
        return v;
    }

    @Override
    public void bindView(View view, Context context, Cursor cursor) {
        ViewHolder holder = (ViewHolder) view.getTag();
        String companyName = cursor.getString(cursor.getColumnIndex(ProjectEntry.COLUMN_COMPANY_NAME));

        holder.projectName.setText(cursor.getString(cursor.getColumnIndex(ProjectEntry.COLUMN_NAME)));
        holder.companyName.setText(companyName == null || companyName.isEmpty() ? context.getString(R.string.non_associate_resource) : companyName);
        holder.bugCount.setText(String.valueOf(cursor.getInt(cursor.getColumnIndex(ProjectEntry.COLUMN_COUNT_BUG))));
        holder.taskCount.setText(String.valueOf(cursor.getInt(cursor.getColumnIndex(ProjectEntry.COLUMN_COUNT_TASK))));
    }
}
