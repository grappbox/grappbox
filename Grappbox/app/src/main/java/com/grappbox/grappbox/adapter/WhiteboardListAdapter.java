package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.WhiteboardModel;

import java.util.ArrayList;


public class WhiteboardListAdapter extends ArrayAdapter<WhiteboardModel> {
    public WhiteboardListAdapter(Context context, ArrayList<WhiteboardModel> resource) {
        super(context, 0, resource);
    }

    @NonNull
    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        if (convertView == null)
            convertView = LayoutInflater.from(getContext()).inflate(R.layout.list_item_whiteboard, parent, false);
        WhiteboardModel model = getItem(position);
        if (model == null)
            return super.getView(position, convertView, parent);
        ((TextView) convertView.findViewById(R.id.name)).setText(model.name);
        return convertView;
    }
}
