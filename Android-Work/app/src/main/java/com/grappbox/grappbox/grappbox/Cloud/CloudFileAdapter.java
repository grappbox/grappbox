package com.grappbox.grappbox.grappbox.Cloud;

import android.content.Context;
import android.graphics.drawable.Drawable;
import android.support.v4.content.ContextCompat;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.ImageView;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

import java.util.Comparator;
import java.util.List;

/**
 * Created by wieser_m on 17/01/2016.
 */
public class CloudFileAdapter extends ArrayAdapter<FileItem> {

    public CloudFileAdapter(Context context, int textViewResourceId) {
        super(context, textViewResourceId);
    }

    public CloudFileAdapter(Context context, int resource, List<FileItem> items) {
        super(context, resource, items);
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        View v = convertView;

        if (v == null) {
            LayoutInflater vi;
            vi = LayoutInflater.from(getContext());
            v = vi.inflate(R.layout.cloudexplorer_item, null);
        }

        FileItem file = getItem(position);

        if (file != null) {
            TextView name = (TextView) v.findViewById(R.id.cloudexplorer_item_filename);
            ImageView img = (ImageView) v.findViewById(R.id.cloudexplorer_item_filetype);

            if (name != null) {
                name.setText(file.get_filename());
            }
            if (img != null)
            {
                Drawable typeImg = null;
                if (file.get_type() != FileItem.EFileType.BACK)
                    typeImg = ContextCompat.getDrawable(getContext(), (file.get_type() == FileItem.EFileType.FILE ? R.drawable.ic_file : R.drawable.ic_directory));
                else
                    typeImg = ContextCompat.getDrawable(getContext(), R.drawable.ic_cloud_parent);
                img.setImageDrawable(typeImg);
            }
        }

        return v;
    }

    @Override
    public void notifyDataSetChanged() {

        super.notifyDataSetChanged();
    }
}
