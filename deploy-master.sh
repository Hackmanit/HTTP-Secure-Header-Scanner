#!/bin/bash
set -e

docker tag "$DOCKER_IMAGE" "$DOCKER_REPO"/"$DOCKER_IMAGE":latest
docker push "$DOCKER_REPO"/"$DOCKER_IMAGE":latest

echo $GCLOUD_KEY | base64 --decode -i > ${HOME}/gcloud-service-key.json
gcloud auth activate-service-account --key-file ${HOME}/gcloud-service-key.json
gcloud --quiet config set project $PROJECT_NAME_PRD
gcloud --quiet config set container/cluster $CLUSTER_NAME_PRD
gcloud --quiet config set compute/zone ${CLOUDSDK_COMPUTE_ZONE}
gcloud --quiet container clusters get-credentials $CLUSTER_NAME_PRD

kubectl patch deployment hshs-domxss-scanner --namespace production -p \
  "{\"spec\":{\"template\":{\"metadata\":{\"labels\":{\"date\":\"`date +'%s'`\", \"commit\":\"$TRAVIS_COMMIT\"}}}}}"
