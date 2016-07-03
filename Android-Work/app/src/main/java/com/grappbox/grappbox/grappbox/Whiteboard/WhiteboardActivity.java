package com.grappbox.grappbox.grappbox.Whiteboard;

import android.app.Activity;
import android.app.AlarmManager;
import android.app.Dialog;
import android.app.PendingIntent;
import android.content.BroadcastReceiver;
import android.content.ContentValues;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.util.Log;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.BaseAdapter;
import android.widget.EditText;
import android.widget.GridView;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.Spinner;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.BugTracker.EditCommentTask;
import com.grappbox.grappbox.grappbox.R;

import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.List;
import java.util.TimeZone;

/**
 * Created by tan_f on 29/06/2016.
 */
public class WhiteboardActivity extends AppCompatActivity implements View.OnClickListener {

    private int             _SizeSpinnerSelected = 0;
    private int             _timeRefresh = 3000;

    private DrawingView     _DrawView;
    private String          _idWhiteboard;
    private String          _dateWithboard;
    private TextView        _WhiteboardTitle;
    private ImageButton     _ColorBorderBtn;
    private ImageButton     _ColorBtn;
    private ImageButton     _DrawBtn;
    private ImageButton     _EraseButton;
    private ImageButton     _MoveButton;

    private MyReceiver      _receiver;
    private Activity        _context;
    private PendingIntent   _pendingIntent;
    private String          _lastUpadte;

    private float t = 0;

    enum Shape{
        RECTANGLE,
        OVAL,
        LINE,
        FREEHAND,
        LOSANGE,
        TEXT,
        ERASE
    }

    public class MyReceiver extends BroadcastReceiver
    {

        public static final String ACTION_RESP ="";

        @Override
        public void onReceive(Context context, Intent intent)
        {
                Log.v("Call", String.valueOf(t++));
                pullWhiteboard();
        }
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.fragment_whiteboard);
        _context = this;
        _idWhiteboard = getIntent().getStringExtra("idWhiteboard");
        _dateWithboard = getIntent().getStringExtra("createdAt");
        _DrawView = (DrawingView) this.findViewById(R.id.drawing);
        if (_DrawView == null)
            return ;
        _DrawView.setIdWhiteboard(_idWhiteboard);
        _DrawBtn = (ImageButton)findViewById(R.id.draw_btn);
        if (_DrawBtn == null)
            return ;
        _DrawBtn.setOnClickListener(this);
        _ColorBtn = (ImageButton)findViewById(R.id.color_btn);
        if (_ColorBtn == null)
            return ;
        _ColorBtn.setOnClickListener(this);
        _ColorBorderBtn = (ImageButton)findViewById(R.id.color_border_btn);
        if (_ColorBorderBtn == null)
            return ;
        _ColorBorderBtn.setOnClickListener(this);
        _EraseButton = (ImageButton)findViewById(R.id.erase_btn);
        if (_EraseButton == null)
            return ;
        _EraseButton.setOnClickListener(this);
        _MoveButton = (ImageButton)findViewById(R.id.move_btn);
        if (_MoveButton == null)
            return ;
        _MoveButton.setOnClickListener(this);
        _WhiteboardTitle = (TextView)findViewById(R.id.whiteboard_title);
        _WhiteboardTitle.setText(getIntent().getStringExtra("title"));

        _lastUpadte = _dateWithboard;

        _context = this;

        _receiver = new MyReceiver();
        Intent msgIntent = new Intent(_context, WhiteboardPullIntentService.class);
        _pendingIntent = PendingIntent.getService(_context, 0, msgIntent, 0);
        AlarmManager alarmManager = (AlarmManager)_context.getSystemService(Context.ALARM_SERVICE);
        alarmManager.setRepeating(AlarmManager.RTC_WAKEUP, System.currentTimeMillis(), _timeRefresh, _pendingIntent);
        _context.startService(msgIntent);
    }

    public void pullWhiteboard()
    {
        SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        TimeZone timezone = TimeZone.getTimeZone("UTC");
        final Calendar c = Calendar.getInstance(timezone);
        dateFormat.setTimeZone(timezone);

        String date;
        c.add(Calendar.HOUR, +2);
        date = dateFormat.format(c.getTime());
        APIRequestOpenWhiteboard apiRequest = new APIRequestOpenWhiteboard(this);
        apiRequest.execute(_idWhiteboard, _lastUpadte);
        _lastUpadte = date;
    }

    public void updatePush()
    {
        SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");
        String date;
        TimeZone timezone = TimeZone.getTimeZone("UTC");
        final Calendar c = Calendar.getInstance(timezone);

        date = dateFormat.format(c.getTime());
        _lastUpadte = date;
    }

    @Override
    public void onResume() {
        super.onResume();
        IntentFilter filter = new IntentFilter(MyReceiver.ACTION_RESP);
        filter.addCategory(Intent.CATEGORY_DEFAULT);
        AlarmManager alarmManager = (AlarmManager)_context.getSystemService(Context.ALARM_SERVICE);
        alarmManager.setRepeating(AlarmManager.RTC_WAKEUP, System.currentTimeMillis(), 5000, _pendingIntent);
        _context.registerReceiver(_receiver, filter);
    }

    @Override
    public void onPause()
    {
        super.onPause();
        _context.unregisterReceiver(_receiver);
        AlarmManager alarmManager = (AlarmManager)_context.getSystemService(Context.ALARM_SERVICE);
        alarmManager.cancel(_pendingIntent);
    }

    @Override
    public void onClick(View view)
    {
        if (view.getId() == R.id.color_border_btn) {
            final Dialog colorBorderDialog = new Dialog(_context);
            colorBorderDialog.setTitle("Set Border Color : ");
            _DrawView.onMove(false);
            colorBorderDialog.setContentView(R.layout.color_selection_grid);
            GridView colorGrid = (GridView)colorBorderDialog.findViewById(R.id.gridviewcolor);
            colorGrid.setAdapter(new ImageAdapter(_context));
            colorGrid.setOnItemClickListener(new AdapterView.OnItemClickListener() {
                public void onItemClick(AdapterView<?> parent, View v,
                                        int position, long id) {
                    int borderColor = getResources().getIntArray(R.array.color)[position];
                    _ColorBorderBtn.setColorFilter(borderColor);
                    _DrawView.setSecondColor(borderColor);
                    colorBorderDialog.dismiss();
                }
            });
            colorBorderDialog.show();
        }

        if (view.getId() == R.id.erase_btn){
            _DrawView.onMove(false);
            _DrawView.setFormShape(5);
        }

        if (view.getId() == R.id.color_btn) {
            final Dialog colorDialog = new Dialog(_context);
            colorDialog.setTitle("Set Color : ");
            _DrawView.onMove(false);
            colorDialog.setContentView(R.layout.color_selection_grid);
            GridView colorGrid = (GridView)colorDialog.findViewById(R.id.gridviewcolor);
            colorGrid.setAdapter(new ImageAdapter(_context));
            colorGrid.setOnItemClickListener(new AdapterView.OnItemClickListener() {
                public void onItemClick(AdapterView<?> parent, View v,
                                        int position, long id) {
                    int color = getResources().getIntArray(R.array.color)[position];
                    _ColorBtn.setColorFilter(color);
                    _DrawBtn.setColorFilter(color);
                    _EraseButton.setColorFilter(color);
                    _MoveButton.setColorFilter(color);
                    _DrawView.setColor(color);
                    colorDialog.dismiss();
                }
            });
            colorDialog.show();
        }

        if (view.getId() == R.id.draw_btn){
            final Dialog formDialog = new Dialog(_context);
            formDialog.setTitle("Set form : ");
            formDialog.setContentView(R.layout.form_selection);

            _DrawView.onMove(false);
            final Spinner sizeBrush = (Spinner)formDialog.findViewById(R.id.brush_size_spinner);
            ArrayAdapter<CharSequence> adapter = ArrayAdapter.createFromResource(_context, R.array.size_brush, android.R.layout.simple_spinner_item);
            sizeBrush.setAdapter(adapter);
            sizeBrush.setSelection(_SizeSpinnerSelected);
            ImageButton rectButton = (ImageButton)formDialog.findViewById(R.id.rect_shape);
            rectButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _DrawView.setFormShape(0);
                    _SizeSpinnerSelected = sizeBrush.getSelectedItemPosition();
                    _DrawView.setBrushSize(_SizeSpinnerSelected);
                    formDialog.dismiss();
                }
            });

            ImageButton circleButton = (ImageButton)formDialog.findViewById(R.id.circle_shape);
            circleButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _DrawView.setFormShape(1);
                    _SizeSpinnerSelected = sizeBrush.getSelectedItemPosition();
                    _DrawView.setBrushSize(_SizeSpinnerSelected);
                    formDialog.dismiss();
                }
            });

            ImageButton splineButton = (ImageButton)formDialog.findViewById(R.id.spline_shape);
            splineButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _DrawView.setFormShape(2);
                    _SizeSpinnerSelected = sizeBrush.getSelectedItemPosition();
                    _DrawView.setBrushSize(_SizeSpinnerSelected);
                    formDialog.dismiss();
                }
            });

            ImageButton textButton = (ImageButton)formDialog.findViewById(R.id.text_selection);
            textButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _DrawView.setFormShape(3);
                    _SizeSpinnerSelected = sizeBrush.getSelectedItemPosition();
                    _DrawView.setBrushSize(_SizeSpinnerSelected);
                    formDialog.dismiss();
                }
            });

            ImageButton triangleButton = (ImageButton)formDialog.findViewById(R.id.triangle_shape);
            triangleButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _DrawView.setFormShape(4);
                    _SizeSpinnerSelected = sizeBrush.getSelectedItemPosition();
                    _DrawView.setBrushSize(_SizeSpinnerSelected);
                    formDialog.dismiss();
                }
            });

            ImageButton lineButton = (ImageButton)formDialog.findViewById(R.id.line_shape);
            lineButton.setOnClickListener(new View.OnClickListener() {
                @Override
                public void onClick(View v) {
                    _DrawView.setFormShape(6);
                    _SizeSpinnerSelected = sizeBrush.getSelectedItemPosition();
                    _DrawView.setBrushSize(_SizeSpinnerSelected);
                    formDialog.dismiss();
                }
            });

            formDialog.show();
        }

        if (view.getId() == R.id.move_btn)
        {
            _DrawView.onMove(true);
        }
    }

    public void refresh()
    {
        _DrawView.Refresh();
    }

    public void refreshWhiteboard(List<ContentValues> whiteboardForm)
    {
        _DrawView.drawFormWhiteboard(whiteboardForm);
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

    }

}
