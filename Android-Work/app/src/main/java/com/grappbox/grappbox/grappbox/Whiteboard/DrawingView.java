package com.grappbox.grappbox.grappbox.Whiteboard;

import android.app.AlarmManager;
import android.app.AlertDialog;
import android.app.Dialog;
import android.content.BroadcastReceiver;
import android.content.ContentValues;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Paint;
import android.graphics.Path;
import android.graphics.Rect;
import android.graphics.RectF;
import android.graphics.Region;
import android.graphics.Typeface;
import android.os.Bundle;
import android.os.Debug;
import android.support.v4.app.DialogFragment;
import android.support.v4.app.FragmentActivity;
import android.support.v4.app.FragmentManager;
import android.util.AttributeSet;
import android.util.DisplayMetrics;
import android.util.Log;
import android.view.Display;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.ScaleGestureDetector;
import android.view.View;
import android.view.WindowManager;
import android.widget.ArrayAdapter;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.TextView;

import com.grappbox.grappbox.grappbox.R;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;


public class DrawingView extends View {

    private Context _context;
    private String _idWhiteboard = "0";
    private Path _DrawPath;
    private Paint _DrawPaint;
    private Paint _CanvasPaint;
    private Paint _DrawText;
    private int _PaintColor = 0xFF333333;
    private int _SecondColor = 0xFF333333;
    private Canvas _DrawCanvas;
    private Bitmap _CanvasBitmap;
    private Bitmap _WhiteboardBitmap;
    private ArrayList<String> _pointXList = new ArrayList<String>();
    private ArrayList<String> _pointYList = new ArrayList<String>();

    private int _ShapeType = 0;

    private boolean _OnDraw = false;
    private boolean _onMove = false;
    private Region _clip;

    private ArrayList<DrawingShape> _ListShape = new ArrayList<DrawingShape>();

    private static float MIN_ZOOM = 1f;
    private static float MAX_ZOOM = 10f;

    private float _scaleFactor = 1.0f;
    private float touchXStart = 0;
    private float touchYStart = 0;
    private float _previousTouchX = 0;
    private float _previousTouchY = 0;
    private float _decalX = 0;
    private float _decalY = 0;
    private ScaleGestureDetector _detector;


    public DrawingView(Context context, AttributeSet attrs)
    {
        super(context, attrs);
        _context = context;
        setupDraw();

    }

    private void setupDraw()
    {
        _DrawPaint = new Paint();
        _DrawPath = new Path();

        _DrawPaint.setColor(_PaintColor);
        _DrawPaint.setAntiAlias(true);
        _DrawPaint.setStrokeWidth(10);
        _DrawPaint.setStyle(Paint.Style.FILL);
        _DrawPaint.setStrokeJoin(Paint.Join.BEVEL);
        _DrawPaint.setStrokeCap(Paint.Cap.ROUND);

        _DrawText = new Paint();
        _DrawText.setColor(_PaintColor);
        _DrawText.setTextSize(getResources().getDimension(R.dimen.text_little));

        _CanvasPaint = new Paint(Paint.DITHER_FLAG);
        _clip = new Region(0, 0, 3840, 2160);
        _WhiteboardBitmap = Bitmap.createBitmap( 1024, 1024, Bitmap.Config.ARGB_8888);
        _CanvasBitmap = _WhiteboardBitmap;
        _detector = new ScaleGestureDetector(_context, new ScaleListener());
    }

    @Override
    protected void onSizeChanged(int w, int h, int oldw, int oldh)
    {
        super.onSizeChanged(w, h, oldw, oldh);
        _CanvasBitmap = Bitmap.createBitmap(w, h, Bitmap.Config.ARGB_8888);
        _DrawCanvas = new Canvas(_CanvasBitmap);
    }

    @Override
    protected void onDraw(Canvas canvas)
    {
        if (_OnDraw) {
            _CanvasBitmap = _WhiteboardBitmap;
        }
        canvas.drawBitmap(_WhiteboardBitmap, 0, 0, _CanvasPaint);

        for (DrawingShape shape : _ListShape) {
            if (shape.getTypeShape() == DrawingShape.typeShape.SHAPE) {
                canvas.drawPath(shape.getPath(), shape.getInPaint());
                canvas.drawPath(shape.getPath(), shape.getOutPaint());
            } else {
                canvas.drawText(shape.getText(), shape.getRectPosition().left, shape.getRectPosition().top, shape.getInPaint());
            }
        }

        if (_ShapeType == 2) {
            _DrawPaint.setStyle(Paint.Style.STROKE);
            _DrawPaint.setColor(_PaintColor);
            canvas.drawPath(_DrawPath, _DrawPaint);
        } else if (_ShapeType != 5) {
            _DrawPaint.setStyle(Paint.Style.FILL);
            _DrawPaint.setColor(_PaintColor);
            canvas.drawPath(_DrawPath, _DrawPaint);
            _DrawPaint.setStyle(Paint.Style.STROKE);
            _DrawPaint.setColor(_SecondColor);
            canvas.drawPath(_DrawPath, _DrawPaint);
            _DrawPath.reset();
        } else {
            canvas.drawPath(_DrawPath, _DrawPaint);
            _DrawPath.reset();
        }
    }

    private boolean intersect(Path shape)
    {
        Region reg1 = new Region();
        reg1.setPath(shape, _clip);
        Region reg2 = new Region();
        reg2.setPath(_DrawPath, _clip);
        if (!reg1.quickReject(reg2) && reg1.op(reg2, Region.Op.INTERSECT))
            return true;
        return false;
    }

    @Override
    public boolean onTouchEvent(MotionEvent event)
    {
        int count = event.getPointerCount();
        float touchX = event.getX();
        float touchY = event.getY();

        invalidate();
        switch (event.getAction())
        {
            case MotionEvent.ACTION_DOWN:
                touchXStart = event.getX();
                touchYStart = event.getY();
                _previousTouchX = touchXStart;
                _previousTouchY = touchYStart;
                if (!_onMove) {
                    if (count == 2) {

                    } else {
                        _OnDraw = true;
                        _DrawPath.moveTo(touchX, touchY);
                        if (_ShapeType == 3) {
                            TextEditDialogFragment textEditDIalogFragment = new TextEditDialogFragment();
                            FragmentManager fm = ((FragmentActivity) _context).getSupportFragmentManager();
                            textEditDIalogFragment.show(fm, "color selection");
                        }
                    }
                }
                break;

            case MotionEvent.ACTION_MOVE:
                if (!_onMove) {
                    _OnDraw = true;
                    drawShape(touchX, touchY);
                    if (_ShapeType == 5) {
                        _DrawPath.reset();
                        drawShape(touchX, touchY);
                        for (Iterator<DrawingShape> it = _ListShape.iterator(); it.hasNext(); ) {
                            DrawingShape shape = it.next();
                            if (intersect(shape.getPath())) {
                                it.remove();
                            }
                        }
                    }
                } else {
                    if (count < 2) {

                        float moveX = touchX - _previousTouchX;
                        float moveY = touchY - _previousTouchY;

                        for (DrawingShape shape : _ListShape) {
                            shape.TranslatePath(moveX, moveY);
                        }
                        _decalX += moveX;
                        _decalY += moveY;
                        _previousTouchX = touchX;
                        _previousTouchY = touchY;
                    }
                }
                break;

            case MotionEvent.ACTION_UP:
                if (!_onMove) {
                    _OnDraw = false;
                    _CanvasBitmap = _WhiteboardBitmap;
                    _DrawCanvas.drawBitmap(_CanvasBitmap, 0, 0, _CanvasPaint);
                    DrawingShape newShape = new DrawingShape(DrawingShape.typeShape.SHAPE, new Path(_DrawPath), new RectF(touchXStart - _decalX, touchYStart - _decalY,
                            touchX - _decalX, touchY - _decalY));
                    if (_ShapeType == 2) {
                        _DrawPaint.setStyle(Paint.Style.STROKE);
                        _DrawPaint.setColor(_PaintColor);
                        Paint inPaint = new Paint(_DrawPaint);
                        Paint outPaint = new Paint(_DrawPaint);
                        newShape.setPaint(inPaint, outPaint);
                        newShape.scalePath(_scaleFactor);
                        _ListShape.add(newShape);
                    } else if (_ShapeType != 5) {
                        _DrawPath.reset();
                        drawShape(touchX, touchY);
                        _DrawPaint.setStyle(Paint.Style.FILL);
                        _DrawPaint.setColor(_PaintColor);
                        Paint inPaint = new Paint(_DrawPaint);
                        _DrawPaint.setStyle(Paint.Style.STROKE);
                        _DrawPaint.setColor(_SecondColor);
                        Paint outPaint = new Paint(_DrawPaint);
                        newShape.setPaint(inPaint, outPaint);
                        newShape.scalePath(_scaleFactor);
                        _ListShape.add(newShape);
                    }
                    invalidate();
                    _DrawPath.reset();
                    pushShape(_ShapeType, newShape.getRectPosition(), newShape);
                    _pointXList.clear();
                    _pointYList.clear();
                }
                break;

            default:
                return false;
        }

        _detector.onTouchEvent(event);
        invalidate();

        return true;
    }

    private void pushShape(int shapeType, RectF rect, DrawingShape pushShape)
    {
        String shape;
        Log.v("TypeShape", String.valueOf(shapeType));
        switch (shapeType){
            case 0:
                shape = "RECTANGLE";
                break;

            case 1:
                shape = "ELLIPSE";
                break;

            case 2:
                shape = "HANDWRITE";
                break;

            case 3:
                shape = "TEXT";
                break;

            case 4:
                shape = "DIAMOND";
                break;

            default:
                shape = null;
                break;
        }

        if (shape != null) {
            String text = pushShape.getText();
            float rayX = (rect.right - rect.left) / 2;
            float rayY = (rect.bottom - rect.top) / 2;
            String radius = null;
            if (shape.equals("ELLIPSE"))
                radius = String.valueOf(rayX) + ";" + String.valueOf(rayY);
            String positionStartX = String.valueOf(rect.left);
            String positionStartY = String.valueOf(rect.top);
            String positionEndX = String.valueOf(rect.right);
            String positionEndY = String.valueOf(rect.bottom);
            String pointListX = "";
            String pointListY = "";
            for (int i = 0; i < _pointXList.size(); ++i)
            {
                pointListX += _pointXList.get(i);
                pointListY += _pointYList.get(i);
                if ((i + 1) < _pointXList.size()) {
                    pointListX += ";";
                    pointListY += ";";
                }
            }
            String inColor =  String.format("#%06X", (0xFFFFFF & _PaintColor));
            String outColor = String.format("#%06X", (0xFFFFFF & _SecondColor));
            String italic = String.valueOf(pushShape.isItalicText());
            String bold = String.valueOf(pushShape.isBoldText());
            String sizeText = String.valueOf(pushShape.getSizeText());
            APIRequestWhiteboardPush push = new APIRequestWhiteboardPush(this, "add");
            push.execute(_idWhiteboard, shape, positionStartX, positionStartY, positionEndX, positionEndY, inColor, outColor, text, radius, italic, bold, sizeText, pointListX, pointListY);
        }
    }

    public void errorPush()
    {
        for (Iterator<DrawingShape> it = _ListShape.iterator(); it.hasNext(); ) {
            DrawingShape shape = it.next();
            if (shape.getId() < 0) {
                it.remove();
            }
        }
    }

    public void giveIdShapePush(int id)
    {
        for (DrawingShape shape : _ListShape)
        {
            if (shape.getId() < 0){
                shape.setId(id);
            }
        }
    }

    private void drawDiamond(float touchX, float touchY)
    {
        _DrawPath.moveTo((touchX + touchXStart) / 2, touchYStart);
        _DrawPath.lineTo(touchXStart, (touchY + touchYStart) / 2);
        _DrawPath.lineTo((touchX + touchXStart) / 2, touchY);
        _DrawPath.lineTo(touchX, (touchY + touchYStart) / 2);
        _DrawPath.lineTo((touchX + touchXStart) / 2, touchYStart);
    }

    private void drawDiamond(float touchStartX, float touchStartY,float touchX, float touchY, Path path)
    {
        path.moveTo((touchX + touchStartX) / 2, touchStartY);
        path.lineTo(touchStartX, (touchY + touchStartY) / 2);
        path.lineTo((touchX + touchStartX) / 2, touchY);
        path.lineTo(touchX, (touchY + touchStartY) / 2);
        path.lineTo((touchX + touchStartX) / 2, touchStartY);
    }

    public void drawFormWhiteboard(List<ContentValues> whiteboardForm)
    {
        boolean already;

        for (ContentValues form : whiteboardForm)
        {
            already = false;
            if (form.getAsString("form").equals("add"))
            {
                for (DrawingShape shape : _ListShape)
                {
                    if (shape.getId() == form.getAsInteger("id")) {
                        already = true;
                        break;
                    }
                }
                if (already)
                    continue;
                Path path = new Path();

                float startx = Float.parseFloat(form.getAsString("positionStartX")) - _decalX;
                float starty = Float.parseFloat(form.getAsString("positionStartY")) - _decalY;

                float endx = Float.parseFloat(form.getAsString("positionEndX")) - _decalX;
                float endy = Float.parseFloat(form.getAsString("positionEndY")) - _decalY;

                RectF rect = new RectF(startx, starty, endx, endy);

                _DrawPaint.setStyle(Paint.Style.FILL);
                _DrawPaint.setStrokeJoin(Paint.Join.BEVEL);
                _DrawPaint.setStrokeCap(Paint.Cap.ROUND);
                if (form.getAsString("type").equals("RECTANGLE")) {
                    path.addRect(startx, starty, endx, endy, Path.Direction.CCW);
                } else if (form.getAsString("type").equals("ELLIPSE")) {
                    path.addOval(rect, Path.Direction.CCW);
                } else if (form.getAsString("type").equals("DIAMOND")) {
                    path.setFillType(Path.FillType.EVEN_ODD);
                    drawDiamond(startx, starty, endx, endy, path);
                } else if (form.getAsString("type").equals("HANDWRITE")) {
                    _DrawPaint.setStyle(Paint.Style.STROKE);
                    _DrawPaint.setStrokeJoin(Paint.Join.ROUND);
                    _DrawPaint.setStrokeCap(Paint.Cap.ROUND);
                    try {
                        JSONArray arrayPoints = new JSONArray(form.getAsString("points"));

                        for (int i = 0; i < arrayPoints.length(); ++i){
                            JSONObject point = arrayPoints.getJSONObject(i);
                            if (i == 0)
                                path.moveTo(Float.parseFloat(point.getString("x")), Float.parseFloat(point.getString("y")));
                            path.lineTo(Float.parseFloat(point.getString("x")) - _decalX, Float.parseFloat(point.getString("y")) - _decalY);
                        }
                    } catch (JSONException e){
                        e.printStackTrace();
                    }

                }

                if (!form.getAsString("type").equals("TEXT")) {
                    Paint inPaint = new Paint(_DrawPaint);
                    Paint outPaint = new Paint(_DrawPaint);

                    inPaint.setColor(Color.parseColor(form.getAsString("color")));

                    outPaint.setColor(Color.parseColor(form.getAsString("background")));
                    _ListShape.add(new DrawingShape(DrawingShape.typeShape.SHAPE, new Path(path), rect, inPaint, outPaint, 1.0f));
                    Log.v("form", form.getAsString("type"));
                }
            }
        }
        invalidate();
    }

    private void drawShape(float touchX, float touchY)
    {
        if (_ShapeType == 0) {
            float rectXStart = touchXStart;
            float rectYStart = touchYStart;
            float rectXEnd = touchX;
            float rectYEnd = touchY;

            if (touchXStart > touchX) {
                rectXStart = touchX;
                rectXEnd = touchXStart;
            }
            if (touchYStart > touchY) {
                rectYStart = touchY;
                rectYEnd = touchYStart;
            }
            _DrawPath.addRect(rectXStart, rectYStart, rectXEnd, rectYEnd, Path.Direction.CCW);
        } else if (_ShapeType == 1){
            float ovalXStart = touchXStart;
            float ovalYStart = touchYStart;
            float ovalXEnd = touchX;
            float ovalYEnd = touchY;

            if (touchXStart > touchX) {
                ovalXStart = touchX;
                ovalXEnd = touchXStart;
            }
            if (touchYStart > touchY) {
                ovalYStart = touchY;
                ovalYEnd = touchYStart;
            }
            _DrawPath.addOval(new RectF(ovalXStart, ovalYStart, ovalXEnd, ovalYEnd), Path.Direction.CCW);
        } else if (_ShapeType == 2) {
            _DrawPath.lineTo(touchX, touchY);
            _pointXList.add(String.valueOf(touchX));
            _pointYList.add(String.valueOf(touchY));

        }else if (_ShapeType == 4) {
            _DrawPath.setFillType(Path.FillType.EVEN_ODD);
            drawDiamond(touchX, touchY);
        } else if (_ShapeType == 5){
            _DrawPath.addCircle(touchX, touchY, 100, Path.Direction.CCW);
        }
    }

    public void onMove(boolean isOnMove)
    {
        invalidate();
        _onMove = isOnMove;
    }

    public void setColor(String newColor)
    {
        invalidate();
        _PaintColor = Color.parseColor(newColor);
        _DrawPaint.setColor(_PaintColor);
        _DrawText.setColor(_PaintColor);
    }

    public void setColor(int newColor)
    {
        invalidate();
        _PaintColor = newColor;
        _DrawPaint.setColor(_PaintColor);
        _DrawText.setColor(_PaintColor);
    }

    public void setSecondColor(int newColor)
    {
        invalidate();
        _SecondColor = newColor;
    }

    public void setFormShape(int shapeType)
    {
        invalidate();
        if (shapeType == 2){
            setLineShape();
        } else if(shapeType == 5){
            setEraseShape();
        } else {
            setBasicShape();
        }

        _ShapeType = shapeType;
        _DrawPath.reset();
    }

    public void setBrushSize(int pos)
    {
        float sizeValue;
        String sizeString = getResources().getStringArray(R.array.size_brush)[pos];
        sizeValue = Float.parseFloat(sizeString);
        sizeValue *= 10;
        _DrawPaint.setStrokeWidth(sizeValue);
    }

    private void setEraseShape()
    {
        _DrawPaint.setStrokeWidth(10);
        _DrawPaint.setStyle(Paint.Style.STROKE);
        _DrawPaint.setStrokeJoin(Paint.Join.BEVEL);
        _DrawPaint.setStrokeCap(Paint.Cap.ROUND);
        _DrawPaint.setColor(0xFF333333);
    }

    private void setBasicShape()
    {
        _DrawPaint.setStyle(Paint.Style.FILL);
        _DrawPaint.setStrokeJoin(Paint.Join.BEVEL);
        _DrawPaint.setStrokeCap(Paint.Cap.ROUND);
        _DrawPaint.setColor(_PaintColor);
    }

    private void setLineShape() {
        _DrawPaint.setStyle(Paint.Style.STROKE);
        _DrawPaint.setStrokeJoin(Paint.Join.ROUND);
        _DrawPaint.setStrokeCap(Paint.Cap.ROUND);
        _DrawPaint.setColor(_PaintColor);
    }

    public void setIdWhiteboard(String idWhiteboard)
    {
        _idWhiteboard = idWhiteboard;
    }

    class ScaleListener extends ScaleGestureDetector.SimpleOnScaleGestureListener {

        @Override
        public boolean onScale(ScaleGestureDetector detector) {

            float value = (detector.getScaleFactor() - 1.0f) / 10;
            //_scaleFactor += value;
            /*if (_scaleFactor >= 0.5f && _scaleFactor <= 3) {
                for (DrawingShape shape : _ListShape) {
                    shape.scalePath(value);
                }
                for (DrawingText text : _ListText) {
                    text.scalePath(value);
                }
            }*/
            //_scaleFactor = Math.max(MIN_ZOOM, Math.min(_scaleFactor, MAX_ZOOM));
            Log.v("Scale Factor", String.valueOf(value));
            return true;
        }
    }

    public class TextEditDialogFragment extends DialogFragment {

        EditText _msg;
        CheckBox _italic;
        CheckBox _bold;

        @Override
        public Dialog onCreateDialog(Bundle savedInstanceState) {
            AlertDialog.Builder builder = new AlertDialog.Builder(getActivity());
            LayoutInflater inflater = getActivity().getLayoutInflater();
            View view = inflater.inflate(R.layout.dialog_write_text, null);
            final Spinner spinner = (Spinner) view.findViewById(R.id.text_size_spinner);
            ArrayAdapter<CharSequence> adapter = ArrayAdapter.createFromResource(getActivity(), R.array.size_text, android.R.layout.simple_spinner_item);
            spinner.setAdapter(adapter);
            _msg = (EditText)view.findViewById(R.id.dialog_write_text);
            _bold = (CheckBox)view.findViewById(R.id.bold_text_checkbox);
            _italic = (CheckBox)view.findViewById(R.id.italic_text_checkbox);
            builder.setView(view)
                    .setPositiveButton("Write Text", new DialogInterface.OnClickListener() {
                        @Override
                        public void onClick(DialogInterface dialog, int id) {
                            float textSize;
                            if (_italic.isChecked())
                                _DrawText.setTypeface(Typeface.create(Typeface.DEFAULT, Typeface.ITALIC));
                            if (_bold.isChecked())
                                _DrawText.setTypeface(Typeface.create(Typeface.DEFAULT, Typeface.BOLD));
                            if (_italic.isChecked() && _bold.isChecked())
                                _DrawText.setTypeface(Typeface.create(Typeface.DEFAULT, Typeface.BOLD_ITALIC));
                            if(spinner.getSelectedItemPosition() == 0) {
                                _DrawText.setTextSize(getResources().getDimension(R.dimen.text_little));
                                textSize = 14;
                            } else if (spinner.getSelectedItemPosition() == 1) {
                                _DrawText.setTextSize(getResources().getDimension(R.dimen.text_medium));
                                textSize = 18;
                            } else {
                                _DrawText.setTextSize(getResources().getDimension(R.dimen.text_large));
                                textSize = 22;
                            }
                            String message;
                            Rect bound = new Rect();
                            message = _msg.getText().toString();
                            _DrawText.getTextBounds(message, 0, message.length(), bound);
                            Path textPath = new Path();
                            textPath.addRect(touchXStart, touchYStart, (float) bound.width(), (float) bound.height(), Path.Direction.CCW);
                            Paint textPaint = new Paint(_DrawText);
                            Rect sizeText = new Rect((int)touchXStart, (int)touchYStart, bound.width(), bound.height());
                            DrawingShape newText = new DrawingShape(DrawingShape.typeShape.TEXT, textPath, new RectF(sizeText));
                            newText.setPaint(textPaint, null);
                            newText.setText(message, _italic.isChecked(), _bold.isChecked(), textSize);
                            _ListShape.add(newText);

                            pushShape(_ShapeType, new RectF(sizeText), newText);
                            invalidate();
                        }
                    });
            return builder.create();
        }

    }
}
