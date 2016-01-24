package com.grappbox.grappbox.grappbox.Model;

import android.content.Context;
import android.graphics.drawable.BitmapDrawable;
import android.graphics.drawable.Drawable;
import android.support.v4.content.ContextCompat;
import android.support.v4.content.res.ResourcesCompat;
import android.support.v4.graphics.drawable.DrawableCompat;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.ImageView;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.Cloud.FileItem;
import com.grappbox.grappbox.grappbox.R;

import java.util.List;

/**
 * Created by wieser_m on 24/01/2016.
 */
public class ProjectMenuAdapter extends ArrayAdapter<ProjectModel> {


    public ProjectMenuAdapter(Context context, int resource, List<ProjectModel> objects) {
        super(context, resource, objects);
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        View v = convertView;

        if (v == null) {
            LayoutInflater vi;
            vi = LayoutInflater.from(getContext());
            v = vi.inflate(R.layout.dialog_project_settings, null);
        }

        ProjectModel file = getItem(position);

        if (file != null) {
            TextView name = (TextView) v.findViewById(R.id.txt_name);
            ImageView img = (ImageView) v.findViewById(R.id.img_project_logo);

            if (name != null) {
                name.setText(file.getName());
            }
            if (img != null)
            {
                if (file.getLogo() != null)
                    img.setImageDrawable(new BitmapDrawable(v.getResources(), file.getLogo()));
                else
                    img.setImageDrawable(ResourcesCompat.getDrawable(getContext().getResources(), R.mipmap.icon_launcher, getContext().getTheme()));
            }
        }

        return v;
    }
}
