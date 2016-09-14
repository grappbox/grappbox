package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.database.Cursor;
import android.database.MatrixCursor;
import android.database.MergeCursor;
import android.graphics.drawable.Drawable;
import android.support.v4.content.res.ResourcesCompat;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CursorAdapter;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.data.GrappboxContract.CloudEntry;

import java.util.ArrayList;

/**
 * Created by marcw on 12/09/2016.
 */
public class CloudListAdapter extends CursorAdapter {
    public static final int ITEM_SUBHEADER = 0;
    public static final int ITEM_ENTRY = 1;
    public static final String[] cloudProjection = new String[]{
        CloudEntry.TABLE_NAME + "." + CloudEntry._ID,
        CloudEntry.COLUMN_TYPE,
        CloudEntry.COLUMN_FILENAME,
        CloudEntry.COLUMN_PATH
    };

    public interface CloudAdapterListener{
        void onMoreClicked(int position);
    }

    public static class SubHeaderViewHolder{
        public TextView subheader;

        public SubHeaderViewHolder(View view){
            subheader = (TextView) view.findViewById(R.id.subheader);
        }
    }

    public static class CloudEntryViewHolder{
        public ImageView mIcon;
        public TextView mFilename;
        public ImageButton mMore;

        public CloudEntryViewHolder(View view){
            mIcon = (ImageView) view.findViewById(R.id.ic_type);
            mFilename = (TextView) view.findViewById(R.id.filename);
            mMore = (ImageButton) view.findViewById(R.id.more);
        }
    }

    private CloudAdapterListener mListener = null;

    public CloudListAdapter(Context context, Cursor c, int flags) {
        super(context, c, flags);
    }

    public void setListener(CloudAdapterListener listener){ mListener = listener; }

    @Override
    public int getItemViewType(int position) {
        Cursor cursor = (Cursor) this.getItem(position);

        return cursor.getColumnCount() == 2 ? ITEM_SUBHEADER : ITEM_ENTRY;
    }

    @Override
    public int getViewTypeCount() {
        return 2;
    }

    @Override
    public Cursor swapCursor(Cursor newCursor) {
//        if (newCursor == null || !newCursor.moveToFirst())
//            return super.swapCursor(newCursor);
//
//        MatrixCursor subSafe = new MatrixCursor(new String[]{"_id", "subheader"});
//        subSafe.addRow(new Object[]{0, "Secured directory"});
//        MatrixCursor subDir = new MatrixCursor(new String[]{"_id", "subheader"});
//        subSafe.addRow(new Object[]{0, "Directories"});
//        MatrixCursor subFile = new MatrixCursor(new String[]{"_id", "subheader"});
//        subSafe.addRow(new Object[]{0, "Files"});
//
//        MatrixCursor safe = new MatrixCursor(newCursor.getColumnNames());
//        MatrixCursor dirs = new MatrixCursor(newCursor.getColumnNames());
//        MatrixCursor files = new MatrixCursor(newCursor.getColumnNames());
//        do {
//            int type = newCursor.getInt(newCursor.getColumnIndex(CloudEntry.COLUMN_TYPE));
//            Object[] row = new Object[newCursor.getColumnCount()];
//            int i = 0;
//            for (String colName : newCursor.getColumnNames()) {
//                row[i++] = newCursor.isNull(i) ? null : newCursor.getString(i);
//            }
//            switch (type){
//                case 2:
//                    safe.addRow(row);
//                    break;
//                case 1:
//                    dirs.addRow(row);
//                    break;
//                default:
//                    files.addRow(row);
//            }
//        } while (newCursor.moveToNext());
//
//        ArrayList<Cursor> finals = new ArrayList<>();
//        if (safe.getCount() > 0){
//            finals.add(subSafe);
//            finals.add(safe);
//        }
//        if (dirs.getCount() > 0){
//            finals.add(subDir);
//            finals.add(dirs);
//        }
//        if (files.getCount() > 0){
//            finals.add(subFile);
//            finals.add(files);
//        }
        return super.swapCursor(newCursor);
        //return super.swapCursor(new MergeCursor(finals.toArray(new Cursor[finals.size()])));
    }

    @Override
    public View newView(Context context, Cursor cursor, ViewGroup viewGroup) {
        int layout;
        switch (getItemViewType(cursor.getPosition())){
            case ITEM_ENTRY:
                layout = R.layout.list_item_file_cloudentry;
                break;
            default:
                layout = R.layout.list_item_cloud_subheader;
                break;
        }
        View v = LayoutInflater.from(context).inflate(layout, viewGroup, false);
        v.setTag(getItemViewType(cursor.getPosition()) == ITEM_ENTRY ? new CloudEntryViewHolder(v) : new SubHeaderViewHolder(v));
        return v;
    }

    @Override
    public void bindView(View view, Context context, final Cursor cursor) {
        Object viewHoler = view.getTag();

        if (viewHoler instanceof SubHeaderViewHolder){
            ((SubHeaderViewHolder) viewHoler).subheader.setText(cursor.getString(1));
        } else if (viewHoler instanceof CloudEntryViewHolder){
            Drawable ic = ResourcesCompat.getDrawable(view.getResources(), cursor.getInt(1) > 0 ? R.drawable.ic_folder : R.drawable.ic_file, context.getTheme());
            ((CloudEntryViewHolder) viewHoler).mIcon.setImageDrawable(ic);

            ((CloudEntryViewHolder) viewHoler).mFilename.setText(cursor.getString(2));
            ((CloudEntryViewHolder) viewHoler).mMore.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View view) {
                    if (mListener != null)
                        mListener.onMoreClicked(cursor.getPosition());
                }
            });
        }
    }
}
