package com.grappbox.grappbox.grappbox;

import android.app.Dialog;
import android.app.Fragment;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;

/**
 * Created by Arkanice on 25/11/2015.
 */
public class WhiteboardDrawFragment extends Fragment implements View.OnClickListener {

    private DrawingView _DrawView;
    private ImageButton _ColorBtn;
    private ImageButton _DrawBtn;


    private Dialog _currentDialog;


    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        View v;

        v = inflater.inflate(R.layout.fragment_whiteboard_draw, container, false);

        _DrawView = (DrawingView)v.findViewById(R.id.drawing);

        _DrawBtn = (ImageButton)v.findViewById(R.id.draw_btn);
        _DrawBtn.setOnClickListener(this);
        _ColorBtn = (ImageButton)v.findViewById(R.id.new_btn);
        _ColorBtn.setOnClickListener(this);
        return v;
    }

    public void paintClicked(View view)
    {
        String color = view.getTag().toString();
        _DrawView.setColor(color);
        _currentDialog.dismiss();
    }

    @Override
    public void onClick(View view)
    {
        if (view.getId() == R.id.draw_btn){
            final Dialog formDialog = new Dialog(view.getContext());
            formDialog.setTitle("Set form : ");
            formDialog.setContentView(R.layout.form_selection);

            ImageButton rectButton = (ImageButton)formDialog.findViewById(R.id.rect_shape);
            rectButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _DrawView.setFormShape(0);
                    formDialog.dismiss();
                }
            });

            ImageButton circleButton = (ImageButton)formDialog.findViewById(R.id.circle_shape);
            circleButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _DrawView.setFormShape(1);
                    formDialog.dismiss();
                }
            });

            ImageButton splineButton = (ImageButton)formDialog.findViewById(R.id.spline_shape);
            splineButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _DrawView.setFormShape(2);
                    formDialog.dismiss();
                }
            });

            ImageButton triangleButton = (ImageButton)formDialog.findViewById(R.id.triangle_shape);
            triangleButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _DrawView.setFormShape(4);
                    formDialog.dismiss();
                }
            });

            ImageButton textButton = (ImageButton)formDialog.findViewById(R.id.text_selection);
            textButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _DrawView.setFormShape(3);
                    formDialog.dismiss();
                }
            });

            formDialog.show();
        }
        if (view.getId() == R.id.new_btn) {
            final Dialog colorDialog = new Dialog(view.getContext());
            _currentDialog = colorDialog;
            colorDialog.setTitle("Set Color : ");
            colorDialog.setContentView(R.layout.color_selection);
            colorDialog.show();
        }
    }

}
