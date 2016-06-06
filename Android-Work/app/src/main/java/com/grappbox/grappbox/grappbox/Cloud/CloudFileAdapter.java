package com.grappbox.grappbox.grappbox.Cloud;

import android.content.Context;
import android.graphics.drawable.Drawable;
import android.support.v4.content.ContextCompat;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

import java.util.Comparator;
import java.util.List;

import static android.view.View.GONE;

/**
 * Created by wieser_m on 17/01/2016.
 */
public class CloudFileAdapter extends ArrayAdapter<FileItem> {

    public interface CloudAdapterListener{
        void onInfoButtonClicked(FileItem item);
        void onOtherClick(FileItem item, int position, View convertView, ViewGroup parent);
    }

    private CloudAdapterListener _listener;

    public CloudFileAdapter(Context context, int textViewResourceId) {
        super(context, textViewResourceId);
    }

    public CloudFileAdapter(Context context, int resource, List<FileItem> items) {
        super(context, resource, items);
    }

    public void setListener(CloudAdapterListener listener) { _listener = listener; }

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
            ImageButton infos = (ImageButton) v.findViewById(R.id.btn_infos);

            if (name != null) {
                name.setText(file.get_filename());
                name.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View v) {
                        if (_listener != null)
                            _listener.onOtherClick(file, position, convertView, parent);
                    }
                });
            }
            if (img != null)
            {
                Drawable typeImg = null;
                if (file.get_type() != FileItem.EFileType.BACK)
                    typeImg = ContextCompat.getDrawable(getContext(), (file.get_type() == FileItem.EFileType.FILE ? R.drawable.ic_file : R.drawable.ic_directory));
                else
                    typeImg = ContextCompat.getDrawable(getContext(), R.drawable.ic_cloud_parent);
                img.setImageDrawable(typeImg);
                img.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View v) {
                        if (_listener != null)
                            _listener.onOtherClick(file, position, convertView, parent);
                    }
                });
            }
            if (infos != null)
            {
                if (file.get_type() == FileItem.EFileType.BACK || file.get_type() == FileItem.EFileType.DIR)
                {
                    infos.setVisibility(GONE);
                    LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT, 5);
                    img.setLayoutParams(params);
                }

                infos.setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View v) {
                        if (_listener != null)
                            _listener.onInfoButtonClicked(file);
                    }
                });
            }
        }

        return v;
    }

    @Override
    public void notifyDataSetChanged() {

        super.notifyDataSetChanged();
    }
}
