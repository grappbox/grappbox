package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.database.Cursor;
import android.database.MatrixCursor;
import android.database.MergeCursor;
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
    private static final int ITEM_TYPE_EXPLANATION = 0;
    private static final int ITEM_TYPE_PROJECT = 1;

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
    public int getItemViewType(int position) {
        return (position == 0 ? ITEM_TYPE_EXPLANATION : ITEM_TYPE_PROJECT);
    }

    @Override
    public int getViewTypeCount() {
        return 2;
    }

    @Override
    public View newView(Context context, Cursor cursor, ViewGroup parent) {
        int layout;

        switch (getItemViewType(cursor.getPosition())){
            case ITEM_TYPE_EXPLANATION:
                layout = R.layout.list_item_choose_project_tutorial;
                break;
            default:
                layout = R.layout.list_item_project_choosing;
                break;
        }
        View v = LayoutInflater.from(context).inflate(layout, parent, false);
        if (getItemViewType(cursor.getPosition()) != ITEM_TYPE_EXPLANATION)
        {
            ViewHolder holder = new ViewHolder(v);
            v.setTag(holder);
        }

        return v;
    }

    @Override
    public Cursor swapCursor(Cursor newCursor) {
        MatrixCursor tutorial = new MatrixCursor(new String[]{"_id", "name"});
        tutorial.addRow(new Object[]{-1, "tutorial"});
        MergeCursor mergedData = new MergeCursor(new Cursor[]{tutorial, newCursor});
        return super.swapCursor(mergedData);
    }

    @Override
    public void bindView(View view, Context context, Cursor cursor) {
        if (getItemViewType(cursor.getPosition()) == ITEM_TYPE_EXPLANATION)
            return;
        ViewHolder holder = (ViewHolder) view.getTag();
        String companyName = cursor.getString(cursor.getColumnIndex(ProjectEntry.COLUMN_COMPANY_NAME));

        holder.projectName.setText(cursor.getString(cursor.getColumnIndex(ProjectEntry.COLUMN_NAME)));
        holder.companyName.setText(companyName == null || companyName.isEmpty() ? context.getString(R.string.non_associate_resource) : companyName);
        holder.bugCount.setText(String.valueOf(cursor.getInt(cursor.getColumnIndex(ProjectEntry.COLUMN_COUNT_BUG))));
        holder.taskCount.setText(String.valueOf(cursor.getInt(cursor.getColumnIndex(ProjectEntry.COLUMN_COUNT_TASK))));
    }
}
