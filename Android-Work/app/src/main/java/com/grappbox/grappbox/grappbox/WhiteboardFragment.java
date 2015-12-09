package com.grappbox.grappbox.grappbox;


import android.app.AlertDialog;
import android.app.Dialog;
import android.content.Context;
import android.content.DialogInterface;
import android.os.Bundle;
import android.support.v4.app.DialogFragment;
import android.support.v4.app.Fragment;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.BaseAdapter;
import android.widget.GridView;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.Spinner;
import android.widget.Toast;


/**
 * A simple {@link Fragment} subclass.
 */
public class WhiteboardFragment extends Fragment implements View.OnClickListener {

    private DrawingView _DrawView;
    private ImageButton _ColorBorderBtn;
    private ImageButton _ColorBtn;
    private ImageButton _DrawBtn;
    private View        _view;

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
        _ColorBorderBtn = (ImageButton)_view.findViewById(R.id.color_border_btn);
        _ColorBorderBtn.setOnClickListener(this);

        return _view;
    }

    @Override
    public void onClick(View view)
    {

        if (view.getId() == R.id.color_border_btn) {
            final Dialog colorBorderDialog = new Dialog(getActivity());
            colorBorderDialog.setTitle("Set Border Color : ");
            colorBorderDialog.setContentView(R.layout.color_selection_grid);
            GridView colorGrid = (GridView)colorBorderDialog.findViewById(R.id.gridviewcolor);
            colorGrid.setAdapter(new ImageAdapter(getActivity()));
            colorGrid.setOnItemClickListener(new AdapterView.OnItemClickListener() {
                public void onItemClick(AdapterView<?> parent, View v,
                                        int position, long id) {
                    _DrawView.setSecondColor(getResources().getIntArray(R.array.color)[position]);
                    colorBorderDialog.dismiss();
                }
            });
            colorBorderDialog.show();
        }

        if (view.getId() == R.id.color_btn) {
            final Dialog colorDialog = new Dialog(getActivity());
            colorDialog.setTitle("Set Color : ");
            colorDialog.setContentView(R.layout.color_selection_grid);
            GridView colorGrid = (GridView)colorDialog.findViewById(R.id.gridviewcolor);
            colorGrid.setAdapter(new ImageAdapter(getActivity()));
            colorGrid.setOnItemClickListener(new AdapterView.OnItemClickListener() {
                public void onItemClick(AdapterView<?> parent, View v,
                                        int position, long id) {
                    _DrawView.setColor(getResources().getIntArray(R.array.color)[position]);
                    colorDialog.dismiss();
                }
            });
            colorDialog.show();
        }

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


        Log.v("ID = ", String.valueOf(view.getId()));
    }

    public class ImageAdapter extends BaseAdapter {
        private Context mContext;

        public ImageAdapter(Context c) {
            mContext = c;
        }

        public int getCount() {
            return 25;//mThumbIds.length;
        }

        public Object getItem(int position) {
            return null;
        }

        public long getItemId(int position) {
            return 0;
        }

        // create a new ImageView for each item referenced by the Adapter
        public View getView(int position, View convertView, ViewGroup parent) {
            ImageView imageView;
            if (convertView == null) {
                // if it's not recycled, initialize some attributes
                imageView = new ImageView(mContext);
                imageView.setLayoutParams(new GridView.LayoutParams(85, 85));
                imageView.setScaleType(ImageView.ScaleType.CENTER_CROP);
                imageView.setPadding(8, 8, 8, 8);
                int[] colorArray = getResources().getIntArray(R.array.color);
                imageView.setBackgroundColor((colorArray[position]));
            } else {
                imageView = (ImageView) convertView;
            }

            //imageView.setImageResource(mThumbIds[position]);
            return imageView;
        }

        // references to our images
        /*
        private Integer[] mThumbIds = {
                R.drawable.sample_2, R.drawable.sample_3,
                R.drawable.sample_4, R.drawable.sample_5,
                R.drawable.sample_6, R.drawable.sample_7,
                R.drawable.sample_0, R.drawable.sample_1,
                R.drawable.sample_2, R.drawable.sample_3,
                R.drawable.sample_4, R.drawable.sample_5,
                R.drawable.sample_6, R.drawable.sample_7,
                R.drawable.sample_0, R.drawable.sample_1,
                R.drawable.sample_2, R.drawable.sample_3,
                R.drawable.sample_4, R.drawable.sample_5,
                R.drawable.sample_6, R.drawable.sample_7
        };*/
    }
}
