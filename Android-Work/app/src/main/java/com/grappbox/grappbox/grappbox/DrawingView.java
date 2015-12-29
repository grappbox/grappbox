package com.grappbox.grappbox.grappbox;

import android.app.AlertDialog;
import android.app.Dialog;
import android.content.ContentValues;
import android.content.Context;
import android.content.DialogInterface;
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
import android.util.Log;
import android.util.Pair;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.Spinner;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.List;


public class DrawingView extends View {

    private Context _context;
    private Path _DrawPath;
    private Paint _DrawPaint;
    private Paint _CanvasPaint;
    private Paint _DrawText;
    private int _PaintColor = 0xFF333333;
    private int _SecondColor = 0xFF333333;
    private Canvas _DrawCanvas;
    private Bitmap _CanvasBitmap;
    private Bitmap _WhiteboardBitmap;

    private float touchXStart = 0;
    private float touchYStart = 0;
    private int _ShapeType = 0;

    private boolean _OnDraw = false;
    private Region _clip;

    private ArrayList<DrawingShape> _ListShape = new ArrayList<DrawingShape>();
    private ArrayList<DrawingText> _ListText = new ArrayList<DrawingText>();

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
        _WhiteboardBitmap = Bitmap.createBitmap( 1000 /*3840*/, 1000/*2160*/, Bitmap.Config.ARGB_8888);
        _CanvasBitmap = _WhiteboardBitmap;

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
        //canvas.drawBitmap(_WhiteboardBitmap, new Rect(0, 0, 4096, 2160), new RectF(0, 0, 100, 100), _CanvasPaint);
        if (_OnDraw) {
            _CanvasBitmap = _WhiteboardBitmap;
        }
        canvas.drawBitmap(_WhiteboardBitmap, 0, 0, _CanvasPaint);

        for (DrawingShape shape : _ListShape) {
            canvas.drawPath(shape.getPath(), shape.getInPaint());
            canvas.drawPath(shape.getPath(), shape.getOutPaint());
        }
        for (DrawingText text : _ListText){
            canvas.drawText(text.getText(), text.getSizeText().left, text.getSizeText().top, text.getPaintText());
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
        float touchX = event.getX();
        float touchY = event.getY();

        invalidate();
        switch (event.getAction())
        {
            case MotionEvent.ACTION_DOWN:
                _OnDraw = true;
                //_WhiteboardBitmap = _CanvasBitmap;
                touchXStart = event.getX();
                touchYStart = event.getY();
                _DrawPath.moveTo(touchX, touchY);
                if (_ShapeType == 3) {
                    TextEditDialogFragment textEditDIalogFragment = new TextEditDialogFragment();
                    FragmentManager fm = ((FragmentActivity) _context).getSupportFragmentManager();
                    textEditDIalogFragment.show(fm, "color selection");
                }

                break;

            case MotionEvent.ACTION_MOVE:
                _OnDraw = true;
                drawShape(touchX, touchY);
                if (_ShapeType == 5) {
                    _DrawPath.reset();
                    drawShape(touchX, touchY);
                    for (Iterator<DrawingShape> it = _ListShape.iterator(); it.hasNext();) {
                        DrawingShape shape = it.next();
                        if (intersect(shape.getPath())) {
                            it.remove();
                        }
                    }
                    for (Iterator<DrawingText> it = _ListText.iterator(); it.hasNext();) {
                        DrawingText text = it.next();
                        if (intersect(text.getPathText())) {
                            it.remove();
                        }

                    }
                }
                break;

            case MotionEvent.ACTION_UP:
                _OnDraw = false;
                _CanvasBitmap = _WhiteboardBitmap;
                _DrawCanvas.drawBitmap(_CanvasBitmap, 0, 0, _CanvasPaint);
                if (_ShapeType == 2) {
                    _DrawPaint.setStyle(Paint.Style.STROKE);
                    _DrawPaint.setColor(_PaintColor);
                    Paint inPaint = new Paint(_DrawPaint);
                    Paint outPaint = new Paint(_DrawPaint);
                    _ListShape.add(new DrawingShape(new Path(_DrawPath), inPaint, outPaint));
                } else if (_ShapeType != 5) {
                    _DrawPath.reset();
                    drawShape(touchX, touchY);
                    _DrawPaint.setStyle(Paint.Style.FILL);
                    _DrawPaint.setColor(_PaintColor);
                    Paint inPaint = new Paint(_DrawPaint);
                    _DrawPaint.setStyle(Paint.Style.STROKE);
                    _DrawPaint.setColor(_SecondColor);
                    Paint outPaint = new Paint(_DrawPaint);
                    _ListShape.add(new DrawingShape(new Path(_DrawPath), inPaint, outPaint));
                }
                _DrawPath.reset();

                break;

            default:
                return false;
        }

        invalidate();

        return true;
    }

    private void drawTriangle(float touchX, float touchY)
    {
        _DrawPath.moveTo((touchX + touchXStart) / 2, touchYStart);
        _DrawPath.lineTo(touchXStart, touchY);
        _DrawPath.lineTo(touchX, touchY);
        _DrawPath.lineTo((touchX + touchXStart) / 2, touchYStart);
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
        }else if (_ShapeType == 4) {
            _DrawPath.setFillType(Path.FillType.EVEN_ODD);
            drawTriangle(touchX, touchY);
        } else if (_ShapeType == 5){
            _DrawPath.addCircle(touchX, touchY, 100, Path.Direction.CCW);
        }
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
                            if (_italic.isChecked())
                                _DrawText.setTypeface(Typeface.create(Typeface.DEFAULT, Typeface.ITALIC));
                            if (_bold.isChecked())
                                _DrawText.setTypeface(Typeface.create(Typeface.DEFAULT, Typeface.BOLD));
                            if (_italic.isChecked() && _bold.isChecked())
                                _DrawText.setTypeface(Typeface.create(Typeface.DEFAULT, Typeface.BOLD_ITALIC));
                            if(spinner.getSelectedItemPosition() == 0) {
                                _DrawText.setTextSize(getResources().getDimension(R.dimen.text_little));
                            }else if (spinner.getSelectedItemPosition() == 1) {
                                _DrawText.setTextSize(getResources().getDimension(R.dimen.text_medium));
                            } else {
                                _DrawText.setTextSize(getResources().getDimension(R.dimen.text_large));
                            }
                            String message;
                            Rect bound = new Rect();
                            message = _msg.getText().toString();
                            _DrawText.getTextBounds(message, 0, message.length(), bound);
                            Path textPath = new Path();
                            textPath.addRect(touchXStart, touchYStart, (float) bound.width(), (float) bound.height(), Path.Direction.CCW);
                            Paint textPaint = new Paint(_DrawText);
                            Rect sizeText = new Rect((int)touchXStart, (int)touchYStart, bound.width(), bound.height());
                            _ListText.add(new DrawingText(message, textPath, textPaint, sizeText));
                            invalidate();
                        }
                    });
            return builder.create();
        }

    }
}
