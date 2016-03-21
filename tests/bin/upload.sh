find $TRAVIS_BUILD_DIR/tests/_output/ -name "*.png" | while read file; do
	curl \
	  -F "format=JSON" \
	  -F "upload=@$file" \
  "http://uploads.im/api"
done
