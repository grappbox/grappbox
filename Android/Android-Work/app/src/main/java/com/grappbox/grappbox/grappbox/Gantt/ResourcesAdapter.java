package com.grappbox.grappbox.grappbox.Gantt;

import android.app.Activity;
import android.content.Context;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.CheckBox;
import android.widget.CompoundButton;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by wieser_m on 14/05/2016.
 */
public class ResourcesAdapter extends ArrayAdapter<TaskUser>{
    ArrayList<TaskUser> selection = new ArrayList<>();

    public ResourcesAdapter(Context context, int resource) {
        super(context, resource);
    }

    public ResourcesAdapter(Context context, int resource, int textViewResourceId) {
        super(context, resource, textViewResourceId);
    }

    public ResourcesAdapter(Context context, int resource, TaskUser[] objects) {
        super(context, resource, objects);
    }

    public ResourcesAdapter(Context context, int resource, int textViewResourceId, TaskUser[] objects) {
        super(context, resource, textViewResourceId, objects);
    }

    public ResourcesAdapter(Context context, int resource, List<TaskUser> objects) {
        super(context, resource, objects);
    }

    public ResourcesAdapter(Context context, int resource, int textViewResourceId, List<TaskUser> objects) {
        super(context, resource, textViewResourceId, objects);
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        View v = ((Activity)getContext()).getLayoutInflater().inflate(R.layout.adapter_resource_layout, null);

        ((CheckBox)v.findViewById(R.id.cb_checked)).setOnCheckedChangeListener(new CompoundButton.OnCheckedChangeListener() {
            @Override
            public void onCheckedChanged(CompoundButton buttonView, boolean isChecked) {
                if (isChecked)
                    selection.add(getItem(position));
                else
                    selection.remove(position);
            }
        });
        TaskUser usr = getItem(position);
        ((TextView)v.findViewById(R.id.txt_name)).setText(usr.getFirstname() + " " + usr.getLastname());
        return v;
    }
}
