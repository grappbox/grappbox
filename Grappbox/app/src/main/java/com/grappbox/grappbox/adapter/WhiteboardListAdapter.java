package com.grappbox.grappbox.adapter;

import android.content.Context;
import android.content.Intent;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.TextView;

import com.grappbox.grappbox.R;
import com.grappbox.grappbox.model.WhiteboardModel;
import com.grappbox.grappbox.sync.GrappboxWhiteboardJIT;

import java.util.ArrayList;


public class WhiteboardListAdapter extends ArrayAdapter<WhiteboardModel> {
    private Context mContext;
    public WhiteboardListAdapter(Context context, ArrayList<WhiteboardModel> resource) {
        super(context, 0, resource);
        mContext = context;
    }

    @NonNull
    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        if (convertView == null)
            convertView = LayoutInflater.from(getContext()).inflate(R.layout.list_item_whiteboard, parent, false);
        final WhiteboardModel model = getItem(position);
        if (model == null)
            return super.getView(position, convertView, parent);
        ((TextView) convertView.findViewById(R.id.name)).setText(model.name);
        convertView.findViewById(R.id.delete).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Intent delete = new Intent(mContext, GrappboxWhiteboardJIT.class);
                delete.setAction(GrappboxWhiteboardJIT.ACTION_DELETE);
                delete.putExtra(GrappboxWhiteboardJIT.EXTRA_WHITEBOARD_ID, model.grappboxId);
                mContext.startService(delete);
                remove(model);
                notifyDataSetChanged();
            }
        });
        return convertView;
    }
}
