package com.grappbox.grappbox.grappbox;


import android.app.Dialog;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;


/**
 * A simple {@link Fragment} subclass.
 */
public class WhiteboardFragment extends Fragment implements View.OnClickListener {

    private DrawingView _DrawView;
    private ImageButton _ColorBtn;
    private ImageButton _DrawBtn;
    private View        _view;

    private Dialog _currentDialog;

    public WhiteboardFragment() {
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container,
                             Bundle savedInstanceState) {
        _view = inflater.inflate(R.layout.fragment_whiteboard, container, false);

        _DrawView = (DrawingView)_view.findViewById(R.id.drawing);
        _DrawBtn = (ImageButton)_view.findViewById(R.id.draw_btn);
        _DrawBtn.setOnClickListener(this);
        _ColorBtn = (ImageButton)_view.findViewById(R.id.color_btn);
        _ColorBtn.setOnClickListener(this);

        return _view;
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
            final Dialog formDialog = new Dialog(getActivity());
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
        if (view.getId() == R.id.color_btn) {
            final Dialog colorDialog = new Dialog(getActivity());
            _currentDialog = colorDialog;
            colorDialog.setTitle("Set Color : ");
            colorDialog.setContentView(R.layout.color_selection);
            colorDialog.show();
        }
    }
}
