#include "Body/Settings/ImageUploadWidget.h"

ImageUploadWidget::ImageUploadWidget(QWidget *parent) : QWidget(parent)
{

    _mainLayout = new QHBoxLayout(this);
    _imagePresented = new QGraphicsView();
    _scene = new QGraphicsScene(0,0, 128, 128);
    _filenameSelected = new QLabel(tr("Select a file..."));
    _selectFileBtn = new QPushButton(tr("Select image"));
    _filename = QString("");
    _currentPixmap = NULL;
    _fitImg = new QPushButton(tr("Fit"));
    QVBoxLayout *layoutImage = new QVBoxLayout();

    _imagePresented->setMaximumSize(IMG_PREVIEW_SIZE);
    _imagePresented->setMinimumSize(IMG_PREVIEW_SIZE);
    _imagePresented->setScene(_scene);
    _imagePresented->setHorizontalScrollBarPolicy(Qt::ScrollBarAlwaysOff);
    _imagePresented->setVerticalScrollBarPolicy(Qt::ScrollBarAlwaysOff);

    QObject::connect(_selectFileBtn, SIGNAL(released()), this, SLOT(OnImageSelectTriggered()));
    QObject::connect(_fitImg, SIGNAL(released()), this, SLOT(OnFitTriggered()));
    QObject::connect(_scene, SIGNAL(changed(QList<QRectF>)), this, SLOT(SceneChanged(QList<QRectF>)));

    layoutImage->addWidget(_imagePresented);
    layoutImage->addWidget(_fitImg);
    _mainLayout->addLayout(layoutImage);

    _mainLayout->addWidget(_selectFileBtn);
    _mainLayout->addWidget(_filenameSelected);
}

ImageUploadWidget::~ImageUploadWidget()
{

}

void ImageUploadWidget::setImage(const QString &pixmap)
{
    QByteArray dataimg = QByteArray::fromBase64(pixmap.toStdString().c_str());
    QImage img = QImage::fromData(dataimg, "PNG");
    QPixmap pix = QPixmap::fromImage(img);

    _imagePresented->scene()->clear();
    _currentPixmap = _scene->addPixmap(pix);
    _currentPixmap->setFlag(QGraphicsItem::ItemIsSelectable);
    _currentPixmap->setFlag(QGraphicsItem::ItemIsMovable);
    _currentPixmap->setFlag(QGraphicsItem::ItemSendsScenePositionChanges);
    _imagePresented->update();
}

const QPixmap   &ImageUploadWidget::getImage()
{
    _lastTakenImage = _imagePresented->grab();
    return _lastTakenImage;
}

QString ImageUploadWidget::getEncodedImage()
{
    QBuffer encodedImage;

    _lastTakenImage = _imagePresented->grab();
    _lastTakenImage.save(&encodedImage, "PNG");
    return QString(encodedImage.buffer().toBase64().toStdString().c_str());
}

bool            ImageUploadWidget::isImageFromComputer()
{
    return (_filename != "");
}

void ImageUploadWidget::OnImageSelectTriggered()
{
    _filename =  QFileDialog::getOpenFileName(this, tr("Open Image"), QStandardPaths::displayName(QStandardPaths::PicturesLocation), tr("Image Files (*.png *.jpg *.bmp)"));
    _filenameSelected->setText(_filename);

    if (_filename != NULL && _filename != "")
    {
        QPixmap img(_filename);

        _imagePresented->scene()->clear();
        _currentPixmap = _scene->addPixmap(img);
        _currentPixmap->setFlag(QGraphicsItem::ItemIsSelectable);
        _currentPixmap->setFlag(QGraphicsItem::ItemIsMovable);
        _currentPixmap->setFlag(QGraphicsItem::ItemSendsScenePositionChanges);
        _imagePresented->update();
        emit OnImageSelected();
    }
}

void ImageUploadWidget::OnFitTriggered()
{
    QPixmap pix;
    if (!_currentPixmap)
        return;

    pix = _currentPixmap->pixmap().copy().scaled(IMG_PREVIEW_SIZE);
    _scene->clear();
    _currentPixmap = _scene->addPixmap(pix);
    _currentPixmap->setFlag(QGraphicsItem::ItemIsSelectable);
    _currentPixmap->setFlag(QGraphicsItem::ItemIsMovable);
    _currentPixmap->setFlag(QGraphicsItem::ItemSendsScenePositionChanges);
    _imagePresented->update();
}

void ImageUploadWidget::SceneChanged(QList<QRectF>)
{
    QPointF pixPos;
    QPointF newPos;
    if (_currentPixmap == NULL || !_currentPixmap->isActive())
        return;

    pixPos = _currentPixmap->pos();
    newPos = pixPos;
    if (pixPos.x() < -(_currentPixmap->pixmap().size().width() - IMG_PREVIEW_WIDTH))
        newPos.setX(-(_currentPixmap->pixmap().size().width() - IMG_PREVIEW_WIDTH));
    else if (pixPos.x() > 0)
        newPos.setX(0);
    if (pixPos.y() < -(_currentPixmap->pixmap().size().height() - IMG_PREVIEW_HEIGHT))
        newPos.setY(-(_currentPixmap->pixmap().size().height() - IMG_PREVIEW_HEIGHT));
    else if (pixPos.y() > 0)
        newPos.setY(0);
    _currentPixmap->setPos(newPos);
}
